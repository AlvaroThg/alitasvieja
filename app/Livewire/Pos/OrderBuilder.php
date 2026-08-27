<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Concerns\HandlesSplitPayments;
use App\Modules\Menu\Models\Category;
use App\Modules\Menu\Models\Product;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Menu\Models\Sauce;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Promotions\Models\Promotion;
use Illuminate\Support\Facades\Log;

class OrderBuilder extends Component
{
    use HandlesSplitPayments;

    public $tableId = null;
    public $tableName = null;
    public $orderType = 'dine_in';

    #[On('table-selected')]
    public function setTable($id = null)
    {
        $this->tableId = $id;
        $this->tableName = $id ? (\App\Models\Table::find($id)->name ?? null) : null;
        $this->orderType = $id ? 'dine_in' : 'takeaway';
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
    public $tempProductWingsCount = 0;
    public $sauceStep = 1;
    public $tempSelectedSauceIds = [];
    public $tempSauceWingCounts = [];
    public $tempSauceSideCounts = [];

    // Modal de Pago (pedidos de cocina: para llevar / delivery, se cobran al momento)
    public $showPaymentModal = false;
    
    // Pedidos pendientes (por cobrar)
    public $showUnpaidOrdersModal = false;
    public $unpaidOrders = [];
    public $pendingOrderId = null;
    public $pendingOrderTotal = 0;

    // Modal cancelar pedido
    public $showCancelOrderModal = false;
    public $orderToCancelId = null;

    /** El cobro puede repartirse entre varios métodos (efectivo + QR, etc.). */
    protected function montoACobrar(): float
    {
        if ($this->pendingOrderId) {
            return (float) $this->pendingOrderTotal;
        }
        return (float) $this->total;
    }

    public function mount()
    {
        $this->categories = Category::where('is_active', true)->get();
        $this->allSauces = Sauce::where('is_active', true)->get();
        
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
        
        $branchId = auth()->user()?->activeBranchId() ?? 1;
        
        $allProducts = Product::where('category_id', $categoryId)
            ->where('is_active', true)
            ->with('variants')
            ->get();
            
        // Filtrar variantes con precio <= 0 en la sucursal activa
        // y descartar productos que se queden sin variantes
        $filteredProducts = $allProducts->filter(function ($product) use ($branchId) {
            $product->setRelation('variants', $product->variants->filter(function ($variant) use ($branchId) {
                return $variant->priceForBranch($branchId) > 0;
            })->values());
            
            return $product->variants->count() > 0;
        })->values();

        $this->products = $filteredProducts;
    }

    /**
     * Precio a mostrar/cobrar para una variante según la sucursal activa.
     */
    public function priceFor($variant)
    {
        $branchId = auth()->user()?->activeBranchId() ?? 1;
        return $variant->priceForBranch($branchId);
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

        // Unir productos idénticos: misma variante y sin notas especiales
        foreach ($this->cart as $i => $existing) {
            if ($existing['variant_id'] === $variant->id && empty($existing['notes'])) {
                // Validar stock antes de sumar
                if ($stock !== null) {
                    $inCart = collect($this->cart)->where('variant_id', $variant->id)->sum('quantity');
                    if ($inCart + 1 > $stock) {
                        $this->dispatch('stock-alert', message: 'Cantidad de Stock insuficiente.');
                        return;
                    }
                }
                
                $this->cart[$i]['quantity']++;
                $this->saveCartToSession();
                
                // Si el producto lleva salsas, abrir el modal para que elijan la nueva salsa
                if (!empty($this->cart[$i]['has_sauces'])) {
                    $this->openSauceModal($i);
                }
                
                return;
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
            
            if (!empty($this->cart[$index]['has_sauces'])) {
                $this->openSauceModal($index);
            }
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
        $qty = (int) ($item['quantity'] ?? 1);
        $this->tempProductMaxSauces = (int) ($item['max_sauces'] ?? 0) * $qty;
        $this->tempProductWingsCount = (int) ($item['wings_count'] ?? 0) * $qty;
        
        // Reset state
        $this->sauceStep = 1;
        $this->tempSelectedSauceIds = [];
        $this->tempSauceWingCounts = [];
        
        // Pre-fill si ya tenía salsas
        if (!empty($item['sauces'])) {
            foreach ($item['sauces'] as $s) {
                $this->tempSelectedSauceIds[] = $s['id'];
                $this->tempSauceWingCounts[$s['id']] = $s['qty'] ?? 0;
                $this->tempSauceSideCounts[$s['id']] = $s['qty_side'] ?? 0;
            }
        }
        
        $this->showSauceModal = true;
    }

    public function toggleSauceSelection($sauceId)
    {
        if (in_array($sauceId, $this->tempSelectedSauceIds)) {
            $this->tempSelectedSauceIds = array_diff($this->tempSelectedSauceIds, [$sauceId]);
        } else {
            if (count($this->tempSelectedSauceIds) < $this->tempProductMaxSauces) {
                $this->tempSelectedSauceIds[] = $sauceId;
            }
        }
    }

    public function goToSauceStep2()
    {
        $this->sauceStep = 2;
        $newCounts = [];
        $newSideCounts = [];
        foreach ($this->tempSelectedSauceIds as $id) {
            $newCounts[$id] = $this->tempSauceWingCounts[$id] ?? 0;
            $newSideCounts[$id] = $this->tempSauceSideCounts[$id] ?? 0;
        }
        $this->tempSauceWingCounts = $newCounts;
        $this->tempSauceSideCounts = $newSideCounts;
    }
    
    public function goToSauceStep1()
    {
        $this->sauceStep = 1;
    }

    public function incrementSauceWings($sauceId)
    {
        $currentSum = array_sum($this->tempSauceWingCounts) + array_sum($this->tempSauceSideCounts);
        if ($currentSum < $this->tempProductWingsCount) {
            $this->tempSauceWingCounts[$sauceId] = ($this->tempSauceWingCounts[$sauceId] ?? 0) + 1;
        }
    }

    public function decrementSauceWings($sauceId)
    {
        if (isset($this->tempSauceWingCounts[$sauceId]) && $this->tempSauceWingCounts[$sauceId] > 0) {
            $this->tempSauceWingCounts[$sauceId]--;
        }
    }

    public function incrementSauceSide($sauceId)
    {
        $currentSum = array_sum($this->tempSauceWingCounts) + array_sum($this->tempSauceSideCounts);
        if ($currentSum < $this->tempProductWingsCount) {
            $this->tempSauceSideCounts[$sauceId] = ($this->tempSauceSideCounts[$sauceId] ?? 0) + 1;
        }
    }

    public function decrementSauceSide($sauceId)
    {
        if (isset($this->tempSauceSideCounts[$sauceId]) && $this->tempSauceSideCounts[$sauceId] > 0) {
            $this->tempSauceSideCounts[$sauceId]--;
        }
    }

    public function updatedTempSauceWingCounts($value, $key)
    {
        $this->enforceSauceLimit($key, 'wing');
    }

    public function updatedTempSauceSideCounts($value, $key)
    {
        $this->enforceSauceLimit($key, 'side');
    }

    private function enforceSauceLimit($changedKey, $type)
    {
        // Convert to integers and prevent negative
        foreach ($this->tempSauceWingCounts as $k => $v) {
            $this->tempSauceWingCounts[$k] = max(0, (int) $v);
        }
        foreach ($this->tempSauceSideCounts as $k => $v) {
            $this->tempSauceSideCounts[$k] = max(0, (int) $v);
        }

        $currentSum = array_sum($this->tempSauceWingCounts) + array_sum($this->tempSauceSideCounts);

        if ($currentSum > $this->tempProductWingsCount) {
            $excess = $currentSum - $this->tempProductWingsCount;
            // Subtract the excess from the recently changed key
            if ($type === 'wing') {
                $this->tempSauceWingCounts[$changedKey] = max(0, $this->tempSauceWingCounts[$changedKey] - $excess);
            } else {
                $this->tempSauceSideCounts[$changedKey] = max(0, $this->tempSauceSideCounts[$changedKey] - $excess);
            }
        }
    }

    public function confirmSauces()
    {
        $mappedSauces = [];
        
        foreach ($this->tempSelectedSauceIds as $id) {
            $sauce = $this->allSauces->firstWhere('id', $id);
            if ($sauce) {
                $mappedSauces[] = [
                    'id' => $sauce->id,
                    'name' => $sauce->name,
                    'qty' => $this->tempSauceWingCounts[$id] ?? 0,
                    'qty_side' => $this->tempSauceSideCounts[$id] ?? 0,
                ];
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

    /**
     * Crea la orden con sus items, descuenta inventario, ocupa la mesa (si aplica)
     * y aplica la promoción. Devuelve la orden ya persistida.
     */
    protected function persistOrder(): \App\Modules\Orders\Models\Order
    {
        $user = auth()->user();
        $branchId = $user->activeBranchId() ?? 1;

        $orderService = app(\App\Modules\Orders\Services\OrderService::class);

        // Crear la orden
        $order = $orderService->createOrder(
            $branchId,
            $this->tableId,
            $user->id,
            $this->orderNotes,
            $this->tableId ? 'dine_in' : $this->orderType
        );

        // Añadir items
        foreach ($this->cart as $item) {
            $saucesData = [];
            if (!empty($item['sauces'])) {
                foreach ($item['sauces'] as $sauce) {
                    if (($sauce['qty'] ?? 0) > 0) {
                        $saucesData[] = [
                            'sauce_id' => $sauce['id'],
                            'quantity' => $sauce['qty'],
                            'is_coated' => true,
                        ];
                    }
                    if (($sauce['qty_side'] ?? 0) > 0) {
                        $saucesData[] = [
                            'sauce_id' => $sauce['id'],
                            'quantity' => $sauce['qty_side'],
                            'is_coated' => false,
                        ];
                    }
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

        return $order;
    }

    protected function resetCartState(): void
    {
        $this->cart = [];
        $this->orderNotes = '';
        $this->selectedPromotionId = null;
        $this->selectedPromotionName = '';
        $this->discountAmount = 0;
        $this->promotionWarning = '';
        $this->saveCartToSession();
    }

    public function submitOrder()
    {
        if (empty($this->cart)) return;

        // Pedido de cocina (para llevar / delivery): se cobra al momento, así que
        // se exige caja abierta antes de armar el cobro.
        if (!$this->tableId) {
            if (!$this->cashIsOpen()) {
                return;
            }
            // Arranca con una sola línea en efectivo por el total: el caso común
            // se confirma sin tocar nada; si pagaron mixto, se agregan líneas.
            $this->iniciarPagos();
            $this->showPaymentModal = true;
            return;
        }

        // Pedido en salón (mesa): se cobra al pedir, imprimir ambos tickets.
        $order = $this->persistOrder();
        $orderId = $order->id;
        $this->resetCartState();
        $this->dispatch('order-saved', urls: [
            route('pos.tickets.cashier', ['order' => $orderId]),
            route('pos.tickets.kitchen', ['order' => $orderId]),
        ]);
    }

    /**
     * Confirma el pago de un pedido de cocina (para llevar/delivery): crea la
     * orden, la cobra con el método elegido e imprime cocina + caja.
     */
    /**
     * ¿Hay caja abierta en la sucursal? Si no, avisa al cajero y devuelve false.
     * Se consulta antes de registrar nada, para no dejar pedidos sin cobrar.
     */
    protected function cashIsOpen(): bool
    {
        $branchId = auth()->user()->activeBranchId() ?? 1;

        try {
            app(\App\Modules\Orders\Services\CheckoutService::class)->requireOpenSession($branchId);
            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->showPaymentModal = false;
            $this->dispatch('pos-error', message: collect($e->errors())->flatten()->first());
            return false;
        }
    }

    public function confirmTakeawayPayment()
    {
        if (empty($this->cart) && !$this->pendingOrderId) {
            $this->showPaymentModal = false;
            return;
        }

        // Se verifica ANTES de crear el pedido: sin caja abierta no se registra nada.
        if (!$this->cashIsOpen()) {
            return;
        }

        if (!$this->pagoCubierto) {
            $this->paymentError = 'Los pagos no cubren el total del pedido.';
            return;
        }

        $this->paymentError = '';
        $order = null;
        $isPendingOrder = (bool) $this->pendingOrderId;

        // El pedido y su cobro van juntos: si el cobro falla, no queda un pedido
        // creado y sin pagar. Antes esto vivía fuera del try y un error dejaba
        // la pantalla congelada sin explicar nada.
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use (&$order, $isPendingOrder) {
                if ($isPendingOrder) {
                    $order = \App\Modules\Orders\Models\Order::find($this->pendingOrderId);
                } else {
                    $order = $this->persistOrder();
                }

                if ((float) $order->total > 0) {
                    app(\App\Modules\Orders\Services\CheckoutService::class)
                        ->processPayment($order, $this->pagosParaCobro());
                } else {
                    $order->update([
                        'status'         => 'paid',
                        'closed_at'      => now(),
                        'payment_method' => $this->pagos[0]['method'] ?? 'cash',
                    ]);
                }
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->paymentError = collect($e->errors())->flatten()->first();
            return;
        } catch (\Throwable $e) {
            Log::error('Cobro de pedido de cocina falló: ' . $e->getMessage());
            $this->paymentError = 'No se pudo registrar el pedido. Revisa e intenta de nuevo.';
            return;
        }

        $orderId = $order->id;
        $this->showPaymentModal = false;
        
        if (!$isPendingOrder) {
            // Pago directo (nuevo pedido): imprimir AMBOS tickets
            $urls = [
                route('pos.tickets.cashier', ['order' => $orderId]),
                route('pos.tickets.kitchen', ['order' => $orderId]),
            ];
            $this->resetCartState();
        } else {
            // Cobrar pedido pendiente: solo ticket de venta
            // (el de cocina ya se imprimió cuando se creó el pedido)
            $urls = [
                route('pos.tickets.cashier', ['order' => $orderId]),
            ];
            $this->pendingOrderId = null;
            $this->pendingOrderTotal = 0;
            $this->loadUnpaidOrders();
        }

        $this->dispatch('order-saved', urls: $urls);
    }

    public function confirmTakeawayUnpaid()
    {
        if (empty($this->cart)) {
            $this->showPaymentModal = false;
            return;
        }

        if (!$this->cashIsOpen()) {
            return;
        }

        $order = null;
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use (&$order) {
                $order = $this->persistOrder();
            });
        } catch (\Throwable $e) {
            Log::error('Registro de pedido de cocina (Por Cobrar) falló: ' . $e->getMessage());
            $this->paymentError = 'No se pudo registrar el pedido. Revisa e intenta de nuevo.';
            return;
        }

        $orderId = $order->id;
        $this->showPaymentModal = false;
        $this->resetCartState();

        // Por cobrar: solo ticket de cocina
        // (el de venta se imprimirá cuando se cobre)
        $this->dispatch('order-saved', urls: [
            route('pos.tickets.kitchen', ['order' => $orderId]),
        ]);
    }

    public function loadUnpaidOrders()
    {
        $branchId = auth()->user()?->activeBranchId() ?? 1;
        $this->unpaidOrders = \App\Modules\Orders\Models\Order::where('branch_id', $branchId)
            ->where('status', 'open')
            ->whereNull('table_id')
            ->orderBy('id', 'asc')
            ->get();
        $this->showUnpaidOrdersModal = true;
    }

    public function payUnpaidOrder($orderId)
    {
        $order = \App\Modules\Orders\Models\Order::find($orderId);
        if (!$order) return;

        if (!$this->cashIsOpen()) {
            return;
        }

        $this->pendingOrderId = $order->id;
        $this->pendingOrderTotal = $order->total;
        
        $this->showUnpaidOrdersModal = false;
        $this->iniciarPagos();
        $this->showPaymentModal = true;
    }

    public function confirmCancelPendingOrder($orderId)
    {
        $this->orderToCancelId = $orderId;
        $this->showCancelOrderModal = true;
    }

    public function cancelPendingOrder()
    {
        if (!$this->orderToCancelId) return;

        $order = \App\Modules\Orders\Models\Order::find($this->orderToCancelId);
        if ($order) {
            try {
                app(\App\Modules\Orders\Services\OrderService::class)->cancelOrder($order);
                session()->flash('message', 'Pedido cancelado correctamente.');
            } catch (\Exception $e) {
                session()->flash('error', 'Error al cancelar el pedido: ' . $e->getMessage());
            }
        }
        
        $this->showCancelOrderModal = false;
        $this->orderToCancelId = null;

        // Refrescar lista de pendientes silenciosamente
        $branchId = auth()->user()?->activeBranchId() ?? 1;
        $this->unpaidOrders = \App\Modules\Orders\Models\Order::where('branch_id', $branchId)
            ->where('status', 'open')
            ->whereNull('table_id')
            ->orderBy('id', 'asc')
            ->get();
            
        if ($this->unpaidOrders->isEmpty()) {
            $this->showUnpaidOrdersModal = false;
        } else {
            $this->showUnpaidOrdersModal = true;
        }
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
