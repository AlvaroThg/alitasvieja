<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Modules\Menu\Models\Category;
use App\Modules\Menu\Models\Product;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Menu\Models\Sauce;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Promotions\Models\Promotion;
use Illuminate\Support\Facades\Log;

class OrderBuilder extends Component
{
    public $tableId = null;
    public $tableName = null;

    #[On('table-selected')]
    public function setTable($id = null)
    {
        $this->tableId = $id;
        $this->tableName = $id ? (\App\Models\Table::find($id)->name ?? null) : null;
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

    // Promociones
    public \Illuminate\Database\Eloquent\Collection $availablePromotions;
    public $selectedPromotionId = null;
    public $selectedPromotionName = '';
    public $discountAmount = 0;
    public $showPromoModal = false;
    public $promotionWarning = '';

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
        $this->loadPromotions();
    }

    public function loadPromotions()
    {
        $user = auth()->user();
        $branchId = $user ? $user->activeBranchId() : null;

        $query = Promotion::active();
        if ($branchId) {
            $query->forBranch($branchId);
        }
        $this->availablePromotions = $query->get();
    }

    public function openPromoModal()
    {
        $this->loadPromotions();
        $this->showPromoModal = true;
    }

    public function selectPromotion($promoId)
    {
        $promo = $this->availablePromotions->firstWhere('id', $promoId);
        if (!$promo) return;

        // No permitir aplicar la promoción si no se cumple el pedido mínimo.
        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $minOrder = $promo->conditions['min_order_total'] ?? null;
        if ($minOrder !== null && $subtotal < (float) $minOrder) {
            $this->promotionWarning = 'No se puede aplicar "' . $promo->name . '": requiere un pedido mínimo de Bs. '
                . number_format((float) $minOrder, 2) . ' (subtotal actual: Bs. ' . number_format($subtotal, 2) . ').';
            $this->showPromoModal = false;
            return; // no se selecciona la promoción
        }

        $this->selectedPromotionId = $promo->id;
        $this->selectedPromotionName = $promo->name;
        $this->promotionWarning = '';
        $this->recalculateDiscount();
        $this->showPromoModal = false;
        $this->saveCartToSession();
    }

    public function removePromotion()
    {
        $this->selectedPromotionId = null;
        $this->selectedPromotionName = '';
        $this->discountAmount = 0;
        $this->promotionWarning = '';
        $this->saveCartToSession();
    }

    public function recalculateDiscount()
    {
        if (!$this->selectedPromotionId) {
            $this->discountAmount = 0;
            // No se limpia el aviso: puede venir de una promoción que se quitó por no cumplir el mínimo.
            return;
        }

        $promo = Promotion::find($this->selectedPromotionId);
        if (!$promo) {
            $this->discountAmount = 0;
            return;
        }

        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        // Condición: pedido mínimo. Si no se cumple, se QUITA la promoción (no queda aplicada con error).
        $minOrder = $promo->conditions['min_order_total'] ?? null;
        if ($minOrder !== null && $subtotal < (float) $minOrder) {
            $this->discountAmount = 0;
            $this->selectedPromotionId = null;
            $this->selectedPromotionName = '';
            $this->promotionWarning = 'No se aplicó "' . $promo->name . '": requiere un pedido mínimo de Bs. '
                . number_format((float) $minOrder, 2) . ' (subtotal actual: Bs. ' . number_format($subtotal, 2) . ').';
            $this->saveCartToSession();
            return;
        }

        $this->promotionWarning = '';

        if ($promo->discount_type === 'percentage') {
            $this->discountAmount = round($subtotal * ($promo->discount_value / 100), 2);
        } elseif ($promo->discount_type === 'fixed') {
            $this->discountAmount = min($promo->discount_value, $subtotal);
        } else {
            $this->discountAmount = 0;
        }
    }

    public function selectCategory($categoryId)
    {
        $this->activeCategoryId = $categoryId;
        $this->activeProductId = null;
        $this->variants = [];
        $this->products = Product::where('category_id', $categoryId)
            ->where('is_active', true)
            ->with('variants.prices')
            ->get();
    }

    /**
     * Precio a mostrar/cobrar para una variante según la sucursal activa.
     * Usa el precio de la sucursal; si no existe, cae al precio base.
     */
    public function priceFor($variant)
    {
        $branchId = auth()->user()?->activeBranchId() ?? 1;
        // Consulta directa: la relación 'prices' no sobrevive al re-render de Livewire.
        $branchPrice = \App\Modules\Menu\Models\ProductPrice::where('product_variant_id', $variant->id)
            ->where('branch_id', $branchId)
            ->value('price');

        return $branchPrice !== null ? $branchPrice : $variant->price;
    }

    public function selectProduct($productId)
    {
        $this->activeProductId = $productId;
        $product = $this->products->firstWhere('id', $productId);
        if ($product) {
            $this->variants = $product->variants;
        }
    }

    /**
     * Stock disponible de una variante en la sucursal activa.
     * Devuelve null si el producto no controla stock (alitas o sin registro de inventario).
     */
    public function availableStock($variantId)
    {
        $variant = ProductVariant::with('product')->find($variantId);
        if (!$variant || !$variant->product || $variant->product->is_wings) {
            return null;
        }
        $branchId = auth()->user()?->activeBranchId() ?? 1;
        $inv = Inventory::where('product_variant_id', $variantId)
            ->where('branch_id', $branchId)
            ->first();

        return $inv ? (int) $inv->stock_quantity : null;
    }

    public function addVariant($variantId)
    {
        $variant = ProductVariant::with(['product', 'prices'])->find($variantId);
        if (!$variant) return;

        // Validar stock disponible (productos con inventario).
        $stock = $this->availableStock($variant->id);
        if ($stock !== null) {
            $inCart = collect($this->cart)->where('variant_id', $variant->id)->sum('quantity');
            if ($inCart + 1 > $stock) {
                $this->dispatch('stock-alert', message: 'Cantidad de Stock de producto insuficiente. Quedan: ' . max(0, $stock) . '.');
                return;
            }
        }

        // Determinar precio por sucursal
        $user = auth()->user();
        $branchId = $user ? $user->activeBranchId() : 1;
        $branchPriceRecord = $variant->prices->firstWhere('branch_id', $branchId);
        $finalPrice = $branchPriceRecord ? $branchPriceRecord->price : $variant->price;

        // Unir productos idénticos: misma variante, sin salsas y sin nota → acumula cantidad.
        if (!$variant->product->has_sauces) {
            foreach ($this->cart as $i => $existing) {
                if ($existing['variant_id'] === $variant->id && empty($existing['sauces']) && empty($existing['notes'])) {
                    $this->cart[$i]['quantity']++;
                    $this->saveCartToSession();
                    return;
                }
            }
        }

        $cartItem = [
            'id' => uniqid(),
            'variant_id' => $variant->id,
            'variant_name' => $variant->name,
            'product_name' => $variant->product->name,
            'price' => $finalPrice,
            'quantity' => 1,
            'notes' => '',
            'has_sauces' => $variant->product->has_sauces,
            'max_sauces' => $variant->max_sauces,
            'wings_count' => (int) $variant->wings_count, // nº de alitas: tope de alitas a bañar
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
            // Validar stock disponible antes de aumentar.
            $stock = $this->availableStock($this->cart[$index]['variant_id']);
            if ($stock !== null) {
                $inCart = collect($this->cart)->where('variant_id', $this->cart[$index]['variant_id'])->sum('quantity');
                if ($inCart + 1 > $stock) {
                    $this->dispatch('stock-alert', message: 'Cantidad de Stock de producto insuficiente. Quedan: ' . max(0, $stock) . '.');
                    return;
                }
            }
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
        // El tope es la cantidad de alitas de la variante (no se puede bañar más
        // alitas de las que existen). Si no hay nº de alitas, cae a max_sauces.
        $this->tempProductMaxSauces = (int) (($item['wings_count'] ?? 0) ?: ($item['max_sauces'] ?? 0));
        
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
        // Se permite dejar vacío (sin cantidad). Solo se guardan las salsas elegidas.
        $mappedSauces = [];
        foreach ($this->tempSelectedSauces as $id => $qty) {
            if ($qty > 0) {
                $sauce = $this->allSauces->firstWhere('id', $id);
                if ($sauce) {
                    $mappedSauces[] = [
                        'id' => $sauce->id,
                        'name' => $sauce->name,
                        'qty' => $qty,
                    ];
                }
            }
        }
        $this->cart[$this->tempCartIndex]['sauces'] = $mappedSauces;
        $this->showSauceModal = false;
        $this->saveCartToSession();
    }

    // --- Totales ---
    public function getSubtotalProperty()
    {
        $subtotal = collect($this->cart)->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });

        $this->recalculateDiscount();

        return $subtotal;
    }

    public function getTotalProperty()
    {
        return max(0, $this->subtotal - $this->discountAmount);
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

        // Descontar inventario al enviar a cocina (helados, bebidas, etc.).
        // Las alitas se ignoran (usan su propio control de stock).
        try {
            app(\App\Modules\Inventory\Services\InventoryService::class)
                ->decrementOnSale($order->load('items'));
        } catch (\Throwable $e) {
            Log::warning('Inventario no descontado: ' . $e->getMessage());
        }

        // Cambiar estado a mesa
        if ($this->tableId) {
            \App\Models\Table::where('id', $this->tableId)->update(['status' => 'occupied']);
        }

        // Aplicar promoción si fue seleccionada
        if ($this->selectedPromotionId) {
            try {
                $promotionEngine = app(\App\Modules\Promotions\Services\PromotionEngine::class);
                $promotionEngine->apply($order, $this->selectedPromotionId);
                $order->refresh();
            } catch (\Exception $e) {
                Log::warning('Promoción no aplicada: ' . $e->getMessage());
            }
        }

        $orderId = $order->id;

        // 4. Limpiar sesión y notificar a la vista
        $this->cart = [];
        $this->orderNotes = '';
        $this->selectedPromotionId = null;
        $this->selectedPromotionName = '';
        $this->discountAmount = 0;
        $this->saveCartToSession();

        $ticketUrl = route('pos.tickets.cashier', ['order' => $orderId]);
        
        $this->dispatch('order-saved', url: $ticketUrl);
    }

    // --- Persistencia Sesión ---
    protected function saveCartToSession()
    {
        session()->put('pos_cart', $this->cart);
        session()->put('pos_notes', $this->orderNotes);
        session()->put('pos_promo_id', $this->selectedPromotionId);
        session()->put('pos_promo_name', $this->selectedPromotionName);
    }

    protected function loadCartFromSession()
    {
        $this->cart = session()->get('pos_cart', []);
        $this->orderNotes = session()->get('pos_notes', '');
        $this->selectedPromotionId = session()->get('pos_promo_id');
        $this->selectedPromotionName = session()->get('pos_promo_name', '');
    }

    public function render()
    {
        return view('livewire.pos.order-builder');
    }
}
