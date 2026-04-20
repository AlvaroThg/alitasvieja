<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Modules\Menu\Models\Category;
use App\Modules\Menu\Models\Product;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Menu\Models\Sauce;
use Illuminate\Support\Facades\Log;

class OrderBuilder extends Component
{
    public $tableId = null;

    #[On('table-selected')]
    public function setTable($id = null)
    {
        $this->tableId = $id;
        // Reiniciar carrito o cargar carrito de mesa existente
        $this->cart = [];
        $this->saveCartToSession();
    }

    // Datos del Menú
    public $categories = [];
    public $activeCategoryId = null;
    
    public $products = [];
    public $activeProductId = null;
    public $variants = [];
    
    public $allSauces = [];

    // Carrito de la Orden
    public $cart = []; // Array of items
    public $orderNotes = '';

    // Modal de Salsas
    public $showSauceModal = false;
    public $tempCartIndex = null;
    public $tempProductMaxSauces = 0;
    public $tempSelectedSauces = []; // [sauce_id => quantity]

    public function mount()
    {
        $this->categories = Category::where('is_active', true)->get();
        $this->allSauces = Sauce::all();
        
        if ($this->categories->count() > 0) {
            $this->selectCategory($this->categories->first()->id);
        }
        
        $this->loadCartFromSession();
    }

    public function selectCategory($categoryId)
    {
        $this->activeCategoryId = $categoryId;
        $this->activeProductId = null;
        $this->variants = [];
        $this->products = Product::where('category_id', $categoryId)
            ->where('is_active', true)
            ->with('variants')
            ->get();
    }

    public function selectProduct($productId)
    {
        $this->activeProductId = $productId;
        $product = $this->products->firstWhere('id', $productId);
        if ($product) {
            $this->variants = $product->variants;
        }
    }

    public function addVariant($variantId)
    {
        $variant = ProductVariant::with('product')->find($variantId);
        if (!$variant) return;

        $cartItem = [
            'id' => uniqid(),
            'variant_id' => $variant->id,
            'variant_name' => $variant->name,
            'product_name' => $variant->product->name,
            'price' => $variant->price,
            'quantity' => 1,
            'notes' => '',
            'has_sauces' => $variant->product->has_sauces,
            'max_sauces' => $variant->product->max_sauces,
            'sauces' => [], // [ ['id' => 1, 'name' => 'BBQ', 'qty' => 2] ]
        ];

        $this->cart[] = $cartItem;
        $this->saveCartToSession();

        if ($cartItem['has_sauces']) {
            $this->openSauceModal(count($this->cart) - 1);
        }
    }

    public function incrementQty($index)
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['quantity']++;
            $this->saveCartToSession();
        }
    }

    public function decrementQty($index)
    {
        if (isset($this->cart[$index])) {
            if ($this->cart[$index]['quantity'] > 1) {
                $this->cart[$index]['quantity']--;
            } else {
                $this->removeItem($index);
            }
            $this->saveCartToSession();
        }
    }

    public function removeItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->saveCartToSession();
    }

    public function updatedCart()
    {
        $this->saveCartToSession();
    }

    public function updatedOrderNotes()
    {
        $this->saveCartToSession();
    }

    // --- Salsas Logic ---
    public function openSauceModal($cartIndex)
    {
        $this->tempCartIndex = $cartIndex;
        $item = $this->cart[$cartIndex];
        $this->tempProductMaxSauces = (int) $item['max_sauces'];
        
        $this->tempSelectedSauces = [];
        foreach ($item['sauces'] as $s) {
            $this->tempSelectedSauces[$s['id']] = $s['qty'];
        }
        
        $this->showSauceModal = true;
    }

    public function getTempSaucesTotal()
    {
        return array_sum($this->tempSelectedSauces);
    }

    public function incrementSauce($sauceId)
    {
        if ($this->getTempSaucesTotal() < $this->tempProductMaxSauces) {
            $this->tempSelectedSauces[$sauceId] = ($this->tempSelectedSauces[$sauceId] ?? 0) + 1;
        }
    }

    public function decrementSauce($sauceId)
    {
        if (isset($this->tempSelectedSauces[$sauceId]) && $this->tempSelectedSauces[$sauceId] > 0) {
            $this->tempSelectedSauces[$sauceId]--;
            if ($this->tempSelectedSauces[$sauceId] === 0) {
                unset($this->tempSelectedSauces[$sauceId]);
            }
        }
    }

    public function confirmSauces()
    {
        if ($this->getTempSaucesTotal() === (int)$this->tempProductMaxSauces || $this->getTempSaucesTotal() > 0) { // Regla de negocio: Obliga exactas o no
             // Mapeamos array al formato del carrito
             $mappedSauces = [];
             foreach ($this->tempSelectedSauces as $id => $qty) {
                 if ($qty > 0) {
                     $sauce = $this->allSauces->firstWhere('id', $id);
                     if ($sauce) {
                         $mappedSauces[] = [
                             'id' => $sauce->id,
                             'name' => $sauce->name,
                             'qty' => $qty
                         ];
                     }
                 }
             }
             $this->cart[$this->tempCartIndex]['sauces'] = $mappedSauces;
             $this->showSauceModal = false;
             $this->saveCartToSession();
        } else {
            // Podrías lanzar un evento de error via Livewire/Alpine si se requiere confirmación exacta
        }
    }

    // --- Totales ---
    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    // --- Persistencia DB ---
    public function submitOrder()
    {
        if (empty($this->cart)) return;

        $user = auth()->user();
        $branchId = $user->activeBranchId() ?? 1;

        $orderService = app(\App\Modules\Orders\Services\OrderService::class);

        // Crear la orden
        $order = $orderService->createOrder(
            $branchId,
            $this->tableId,
            $user->id,
            $this->orderNotes
        );

        // Añadir items
        foreach ($this->cart as $item) {
            $saucesData = [];
            if (!empty($item['sauces'])) {
                foreach ($item['sauces'] as $sauce) {
                    $saucesData[] = [
                        'sauce_id' => $sauce['id'],
                        'quantity' => $sauce['qty'],
                        'is_coated' => true,
                    ];
                }
            }

            $orderService->addItem($order, [
                'product_variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'notes' => $item['notes'] ?? null,
                'sauces' => $saucesData
            ]);
        }

        // Cambiar estado a mesa
        if ($this->tableId) {
            \App\Models\Table::where('id', $this->tableId)->update(['status' => 'occupied']);
        }

        $orderId = $order->id;

        // 4. Limpiar sesión y notificar a la vista
        $this->cart = [];
        $this->orderNotes = '';
        $this->saveCartToSession();

        $ticketUrl = route('pos.tickets.cashier', ['order' => $orderId]);
        $kitchenUrl = route('kitchen.orders.index'); // O abrir el PDF de cocina, pero de momento usa caja
        
        $this->dispatch('order-saved', url: $ticketUrl); // Opcional: pasar kitchenUrl si se desea imprimir dos veces
    }

    // --- Persistencia Sesión ---
    protected function saveCartToSession()
    {
        session()->put('pos_cart', $this->cart);
        session()->put('pos_notes', $this->orderNotes);
    }

    protected function loadCartFromSession()
    {
        $this->cart = session()->get('pos_cart', []);
        $this->orderNotes = session()->get('pos_notes', '');
    }

    public function render()
    {
        return view('livewire.pos.order-builder');
    }
}
