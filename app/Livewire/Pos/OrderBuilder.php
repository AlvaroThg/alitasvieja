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
    public $editingOrderId = null;

    #[On('table-selected')]
    public function setTable($id = null)
    {
        $this->editingOrderId = null;
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
    public $customerName = '';
    public $cashReceived = '';

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
    public $tempIsWingsProduct = false;
    public $tempItemQuantity = 1;
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

    // Modal seleccionar mesa al enviar
    public $showTableSelectModal = false;

    public function getAvailableTablesProperty()
    {
        $branchId = auth()->user()?->activeBranchId() ?? 1;
        return \App\Models\Table::where('branch_id', $branchId)->orderBy('name', 'asc')->get();
    }

    public function selectOrderType($type)
    {
        $this->orderType = $type;
        if ($type !== 'dine_in') {
            $this->tableId = null;
            $this->tableName = null;
        }
    }

    public function selectTable($id)
    {
        if ($id) {
            $table = \App\Models\Table::find($id);
            $this->tableId = $table ? $table->id : null;
            $this->tableName = $table ? $table->name : null;
            $this->orderType = 'dine_in';
        } else {
            $this->tableId = null;
            $this->tableName = null;
        }
        $this->showTableSelectModal = false;
    }

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

        // Unir productos idénticos: misma variante, sin notas especiales y sin salsas configurables
        foreach ($this->cart as $i => $existing) {
            if ($existing['variant_id'] === $variant->id && empty($existing['notes']) && empty($existing['has_sauces'])) {
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
            'has_sauces' => $variant->product->has_sauces || $variant->product->is_wings,
            'max_sauces' => $variant->max_sauces,
            'wings_count' => (int) $variant->wings_count, // nº de alitas: tope de alitas a bañar
            'sauces' => [], // [ ['id' => 1, 'name' => 'BBQ', 'qty' => 2] ]
        ];

        $this->cart[] = $cartItem;
        $this->saveCartToSession();
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
        $qty = max(1, (int) ($item['quantity'] ?? 1));
        $this->tempItemQuantity = $qty;

        $this->tempProductMaxSauces = (int) ($item['max_sauces'] ?? 0) * $qty;
        $this->tempProductWingsCount = (int) ($item['wings_count'] ?? 0) * $qty;
        
        $variantId = $item['variant_id'] ?? null;
        $variant = $variantId ? \App\Modules\Menu\Models\ProductVariant::with('product')->find($variantId) : null;
        $this->tempIsWingsProduct = $variant ? (bool) $variant->product?->is_wings : (($item['wings_count'] ?? 0) > 0);

        // Reset state
        $this->sauceStep = 1;
        $this->tempSelectedSauceIds = [];
        $this->tempSauceWingCounts = [];
        $this->tempSauceSideCounts = [];
        
        // Pre-fill si ya tenía salsas
        if (!empty($item['sauces'])) {
            foreach ($item['sauces'] as $s) {
                $this->tempSelectedSauceIds[] = $s['id'];
                $wPerUnit = (int) floor(($s['qty'] ?? 0) / $qty);
                $sPerUnit = (int) floor(($s['qty_side'] ?? 0) / $qty);
                for ($u = 0; $u < $qty; $u++) {
                    $this->tempSauceWingCounts[$u][$s['id']] = $wPerUnit;
                    $this->tempSauceSideCounts[$u][$s['id']] = $sPerUnit;
                }
            }
        }
        
        $this->showSauceModal = true;
    }

    public function updateTempQuantity($newQty)
    {
        $newQty = max(1, (int) $newQty);
        $this->tempItemQuantity = $newQty;

        if (isset($this->tempCartIndex) && isset($this->cart[$this->tempCartIndex])) {
            $item = $this->cart[$this->tempCartIndex];
            
            // Validar stock si se incrementa
            $stock = $this->availableStock($item['variant_id']);
            if ($stock !== null && $newQty > $item['quantity']) {
                $otherInCart = collect($this->cart)->except($this->tempCartIndex)->sum('quantity');
                if (($otherInCart + $newQty) > $stock) {
                    $this->dispatch('stock-alert', message: 'Cantidad de Stock de producto insuficiente. Quedan: ' . max(0, $stock) . '.');
                    return;
                }
            }

            $this->cart[$this->tempCartIndex]['quantity'] = $newQty;
            $this->saveCartToSession();

            $variantId = $item['variant_id'] ?? null;
            $variant = $variantId ? \App\Modules\Menu\Models\ProductVariant::find($variantId) : null;
            $maxPerUnit = $variant ? (int) $variant->max_sauces : (int) ($item['max_sauces'] ?? 0);
            $wingsPerUnit = $variant ? (int) $variant->wings_count : (int) ($item['wings_count'] ?? 0);

            $this->tempProductMaxSauces = $maxPerUnit * $newQty;
            $this->tempProductWingsCount = $wingsPerUnit * $newQty;
        }
    }

    public function incrementTempQuantity()
    {
        $this->updateTempQuantity($this->tempItemQuantity + 1);
    }

    public function decrementTempQuantity()
    {
        if ($this->tempItemQuantity > 1) {
            $this->updateTempQuantity($this->tempItemQuantity - 1);
        }
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

    public function quickConfirmSingleSauce($sauceId, $isCoated = true)
    {
        $this->tempSelectedSauceIds = [$sauceId];
        $this->setAllUnitsCoated($isCoated);
        $this->confirmSauces();
    }

    public function quickConfirmCurrentSauces($isCoated = true)
    {
        if (empty($this->tempSelectedSauceIds)) return;
        $this->setAllUnitsCoated($isCoated);
        $this->confirmSauces();
    }

    public function copyUnitOneToAll()
    {
        $qty = max(1, (int) $this->tempItemQuantity);
        if ($qty <= 1) return;

        $unitZeroWings = $this->tempSauceWingCounts[0] ?? ($this->tempSauceWingCounts ?? []);
        $unitZeroSide = $this->tempSauceSideCounts[0] ?? ($this->tempSauceSideCounts ?? []);

        for ($u = 1; $u < $qty; $u++) {
            $this->tempSauceWingCounts[$u] = $unitZeroWings;
            $this->tempSauceSideCounts[$u] = $unitZeroSide;
        }
    }

    public function setAllUnitsCoated($isCoated = true)
    {
        $qty = max(1, (int) $this->tempItemQuantity);
        $selectedSauces = array_values($this->tempSelectedSauceIds);
        $selectedCount = count($selectedSauces);
        if ($selectedCount === 0) return;

        $cartItem = $this->cart[$this->tempCartIndex] ?? null;
        $maxPerUnit = max(1, (int) ($cartItem['max_sauces'] ?? 1));
        $wingsPerUnit = $qty > 0 ? (int) ($this->tempProductWingsCount / $qty) : $this->tempProductWingsCount;

        for ($u = 0; $u < $qty; $u++) {
            $this->tempSauceWingCounts[$u] = [];
            $this->tempSauceSideCounts[$u] = [];

            // Determinar qué salsas corresponden a la unidad/porción $u
            if ($selectedCount >= $qty * $maxPerUnit) {
                // Hay suficientes salsas para asignar $maxPerUnit salsas distintas por unidad
                $unitSauceSlice = array_slice($selectedSauces, $u * $maxPerUnit, $maxPerUnit);
            } elseif ($selectedCount == $qty) {
                // Exactamente 1 salsa distinta por unidad (ej: 4 unidades, 4 salsas)
                $unitSauceSlice = isset($selectedSauces[$u]) ? [$selectedSauces[$u]] : [$selectedSauces[0]];
            } elseif ($selectedCount < $qty) {
                // Menos salsas que unidades (ej: 4 unidades, 2 salsas): asignación cíclica
                $unitSauceSlice = [$selectedSauces[$u % $selectedCount]];
            } else {
                // Más salsas que unidades pero menos que Q * M: asignar bloque a cada unidad
                $chunkSize = (int) ceil($selectedCount / $qty);
                $unitSauceSlice = array_slice($selectedSauces, $u * $chunkSize, $chunkSize);
            }

            $uSauceCount = count($unitSauceSlice);
            if ($uSauceCount === 0) continue;

            if ($this->tempIsWingsProduct) {
                $basePerSauce = (int) floor($wingsPerUnit / $uSauceCount);
                $remainder = $wingsPerUnit % $uSauceCount;

                foreach ($unitSauceSlice as $idx => $sauceId) {
                    $amount = $basePerSauce + ($idx < $remainder ? 1 : 0);
                    if ($isCoated) {
                        $this->tempSauceWingCounts[$u][$sauceId] = $amount;
                        $this->tempSauceSideCounts[$u][$sauceId] = 0;
                    } else {
                        $this->tempSauceWingCounts[$u][$sauceId] = 0;
                        $this->tempSauceSideCounts[$u][$sauceId] = $amount;
                    }
                }
            } else {
                foreach ($unitSauceSlice as $sauceId) {
                    if ($isCoated) {
                        $this->tempSauceWingCounts[$u][$sauceId] = 1;
                        $this->tempSauceSideCounts[$u][$sauceId] = 0;
                    } else {
                        $this->tempSauceWingCounts[$u][$sauceId] = 0;
                        $this->tempSauceSideCounts[$u][$sauceId] = 1;
                    }
                }
            }
        }
    }

    public function getPortionSauceLimit(): int
    {
        $qty = max(1, (int) $this->tempItemQuantity);
        if ($this->tempProductWingsCount > 0) {
            return max(1, (int) ($this->tempProductWingsCount / $qty));
        }

        $cartItem = $this->cart[$this->tempCartIndex] ?? null;
        return max(1, (int) ($cartItem['max_sauces'] ?? 1));
    }

    public function goToSauceStep2()
    {
        if (empty($this->tempSelectedSauceIds)) {
            return;
        }

        $this->sauceStep = 2;
    }

    public function updateSauceWings($sauceId, $unitIndex = 0, $value = 0)
    {
        $val = max(0, (int) $value);
        $qty = max(1, (int) $this->tempItemQuantity);
        $portionLimit = $this->getPortionSauceLimit();

        $val = min($val, $portionLimit);

        if ($qty > 1) {
            $u = $unitIndex;
            $this->tempSauceWingCounts[$u][$sauceId] = $val;

            $wingSum = $this->sumSauceCounts($this->tempSauceWingCounts, $u);
            $sideSum = $this->sumSauceCounts($this->tempSauceSideCounts, $u);
            $totalSum = $wingSum + $sideSum;

            if ($totalSum > $portionLimit) {
                $excess = $totalSum - $portionLimit;

                if (isset($this->tempSauceSideCounts[$u]) && is_array($this->tempSauceSideCounts[$u])) {
                    foreach ($this->tempSauceSideCounts[$u] as $sId => $sCount) {
                        if ($excess <= 0) break;
                        $reduce = min($excess, (int)$sCount);
                        $this->tempSauceSideCounts[$u][$sId] = max(0, (int)$sCount - $reduce);
                        $excess -= $reduce;
                    }
                }

                if ($excess > 0 && isset($this->tempSauceWingCounts[$u]) && is_array($this->tempSauceWingCounts[$u])) {
                    foreach ($this->tempSauceWingCounts[$u] as $sId => $wCount) {
                        if ($sId == $sauceId) continue;
                        if ($excess <= 0) break;
                        $reduce = min($excess, (int)$wCount);
                        $this->tempSauceWingCounts[$u][$sId] = max(0, (int)$wCount - $reduce);
                        $excess -= $reduce;
                    }
                }
            }
        } else {
            if (!isset($this->tempSauceWingCounts[0]) || !is_array($this->tempSauceWingCounts[0])) {
                $oldWing = $this->tempSauceWingCounts;
                $this->tempSauceWingCounts = [0 => is_array($oldWing) ? $oldWing : []];
            }
            if (!isset($this->tempSauceSideCounts[0]) || !is_array($this->tempSauceSideCounts[0])) {
                $oldSide = $this->tempSauceSideCounts;
                $this->tempSauceSideCounts = [0 => is_array($oldSide) ? $oldSide : []];
            }

            $this->tempSauceWingCounts[0][$sauceId] = $val;

            $wingSum = array_sum($this->tempSauceWingCounts[0]);
            $sideSum = array_sum($this->tempSauceSideCounts[0]);
            $totalSum = $wingSum + $sideSum;

            if ($totalSum > $portionLimit) {
                $excess = $totalSum - $portionLimit;

                if (isset($this->tempSauceSideCounts[0][$sauceId]) && (int)$this->tempSauceSideCounts[0][$sauceId] > 0) {
                    $reduce = min($excess, (int)$this->tempSauceSideCounts[0][$sauceId]);
                    $this->tempSauceSideCounts[0][$sauceId] -= $reduce;
                    $excess -= $reduce;
                }

                if ($excess > 0) {
                    foreach ($this->tempSauceSideCounts[0] as $sId => $sCount) {
                        if ($excess <= 0) break;
                        $reduce = min($excess, (int)$sCount);
                        $this->tempSauceSideCounts[0][$sId] -= $reduce;
                        $excess -= $reduce;
                    }
                }

                if ($excess > 0) {
                    foreach ($this->tempSauceWingCounts[0] as $sId => $wCount) {
                        if ($sId == $sauceId) continue;
                        if ($excess <= 0) break;
                        $reduce = min($excess, (int)$wCount);
                        $this->tempSauceWingCounts[0][$sId] -= $reduce;
                        $excess -= $reduce;
                    }
                }
            }
        }
    }

    public function updateSauceSide($sauceId, $unitIndex = 0, $value = 0)
    {
        $val = max(0, (int) $value);
        $qty = max(1, (int) $this->tempItemQuantity);
        $portionLimit = $this->getPortionSauceLimit();

        $val = min($val, $portionLimit);

        if ($qty > 1) {
            $u = $unitIndex;
            $this->tempSauceSideCounts[$u][$sauceId] = $val;

            $wingSum = $this->sumSauceCounts($this->tempSauceWingCounts, $u);
            $sideSum = $this->sumSauceCounts($this->tempSauceSideCounts, $u);
            $totalSum = $wingSum + $sideSum;

            if ($totalSum > $portionLimit) {
                $excess = $totalSum - $portionLimit;

                if (isset($this->tempSauceWingCounts[$u]) && is_array($this->tempSauceWingCounts[$u])) {
                    foreach ($this->tempSauceWingCounts[$u] as $sId => $wCount) {
                        if ($excess <= 0) break;
                        $reduce = min($excess, (int)$wCount);
                        $this->tempSauceWingCounts[$u][$sId] = max(0, (int)$wCount - $reduce);
                        $excess -= $reduce;
                    }
                }

                if ($excess > 0 && isset($this->tempSauceSideCounts[$u]) && is_array($this->tempSauceSideCounts[$u])) {
                    foreach ($this->tempSauceSideCounts[$u] as $sId => $sCount) {
                        if ($sId == $sauceId) continue;
                        if ($excess <= 0) break;
                        $reduce = min($excess, (int)$sCount);
                        $this->tempSauceSideCounts[$u][$sId] = max(0, (int)$sCount - $reduce);
                        $excess -= $reduce;
                    }
                }
            }
        } else {
            if (!isset($this->tempSauceWingCounts[0]) || !is_array($this->tempSauceWingCounts[0])) {
                $oldWing = $this->tempSauceWingCounts;
                $this->tempSauceWingCounts = [0 => is_array($oldWing) ? $oldWing : []];
            }
            if (!isset($this->tempSauceSideCounts[0]) || !is_array($this->tempSauceSideCounts[0])) {
                $oldSide = $this->tempSauceSideCounts;
                $this->tempSauceSideCounts = [0 => is_array($oldSide) ? $oldSide : []];
            }

            $this->tempSauceSideCounts[0][$sauceId] = $val;

            $wingSum = array_sum($this->tempSauceWingCounts[0]);
            $sideSum = array_sum($this->tempSauceSideCounts[0]);
            $totalSum = $wingSum + $sideSum;

            if ($totalSum > $portionLimit) {
                $excess = $totalSum - $portionLimit;

                if (isset($this->tempSauceWingCounts[0][$sauceId]) && (int)$this->tempSauceWingCounts[0][$sauceId] > 0) {
                    $reduce = min($excess, (int)$this->tempSauceWingCounts[0][$sauceId]);
                    $this->tempSauceWingCounts[0][$sauceId] -= $reduce;
                    $excess -= $reduce;
                }

                if ($excess > 0) {
                    foreach ($this->tempSauceWingCounts[0] as $sId => $wCount) {
                        if ($excess <= 0) break;
                        $reduce = min($excess, (int)$wCount);
                        $this->tempSauceWingCounts[0][$sId] -= $reduce;
                        $excess -= $reduce;
                    }
                }

                if ($excess > 0) {
                    foreach ($this->tempSauceSideCounts[0] as $sId => $sCount) {
                        if ($sId == $sauceId) continue;
                        if ($excess <= 0) break;
                        $reduce = min($excess, (int)$sCount);
                        $this->tempSauceSideCounts[0][$sId] -= $reduce;
                        $excess -= $reduce;
                    }
                }
            }
        }
    }

    public function setSauceCoated($sauceId, $isCoated, $unitIndex = 0)
    {
        if ($this->tempItemQuantity > 1) {
            if ($isCoated) {
                $this->tempSauceWingCounts[$unitIndex][$sauceId] = 1;
                $this->tempSauceSideCounts[$unitIndex][$sauceId] = 0;
            } else {
                $this->tempSauceWingCounts[$unitIndex][$sauceId] = 0;
                $this->tempSauceSideCounts[$unitIndex][$sauceId] = 1;
            }
        } else {
            if ($isCoated) {
                $this->tempSauceWingCounts[$sauceId] = 1;
                $this->tempSauceSideCounts[$sauceId] = 0;
            } else {
                $this->tempSauceWingCounts[$sauceId] = 0;
                $this->tempSauceSideCounts[$sauceId] = 1;
            }
        }
    }
    
    public function goToSauceStep1()
    {
        $this->sauceStep = 1;
    }

    public function sumSauceCounts(array $countsArray, ?int $unitIndex = null): int
    {
        if ($unitIndex !== null) {
            if (isset($countsArray[$unitIndex])) {
                return is_array($countsArray[$unitIndex]) ? array_sum($countsArray[$unitIndex]) : (int) $countsArray[$unitIndex];
            }
            return 0;
        }

        $total = 0;
        foreach ($countsArray as $val) {
            if (is_array($val)) {
                $total += array_sum($val);
            } else {
                $total += (int) $val;
            }
        }
        return $total;
    }

    public function getTotalWingsAssignedProperty(): int
    {
        return $this->sumSauceCounts($this->tempSauceWingCounts) + $this->sumSauceCounts($this->tempSauceSideCounts);
    }

    public function getTempSauceWingsTotalProperty(): int
    {
        return $this->sumSauceCounts($this->tempSauceWingCounts);
    }

    public function incrementSauceWings($sauceId, $unitIndex = 0)
    {
        $qty = max(1, (int) $this->tempItemQuantity);
        $portionLimit = $this->getPortionSauceLimit();

        if ($qty > 1) {
            $unitWingsSum = $this->sumSauceCounts($this->tempSauceWingCounts, $unitIndex)
                          + $this->sumSauceCounts($this->tempSauceSideCounts, $unitIndex);

            if ($unitWingsSum < $portionLimit) {
                $this->tempSauceWingCounts[$unitIndex][$sauceId] = ($this->tempSauceWingCounts[$unitIndex][$sauceId] ?? 0) + 1;
            }
        } else {
            $currentWingsSum = $this->sumSauceCounts($this->tempSauceWingCounts)
                             + $this->sumSauceCounts($this->tempSauceSideCounts);
            $totalLimit = $this->tempProductWingsCount > 0 ? $this->tempProductWingsCount : $portionLimit;

            if ($currentWingsSum < $totalLimit) {
                if (isset($this->tempSauceWingCounts[0]) && is_array($this->tempSauceWingCounts[0])) {
                    $this->tempSauceWingCounts[0][$sauceId] = ($this->tempSauceWingCounts[0][$sauceId] ?? 0) + 1;
                } else {
                    $this->tempSauceWingCounts[$sauceId] = ($this->tempSauceWingCounts[$sauceId] ?? 0) + 1;
                }
            }
        }
    }

    public function decrementSauceWings($sauceId, $unitIndex = 0)
    {
        if ($this->tempItemQuantity > 1) {
            if (isset($this->tempSauceWingCounts[$unitIndex][$sauceId]) && $this->tempSauceWingCounts[$unitIndex][$sauceId] > 0) {
                $this->tempSauceWingCounts[$unitIndex][$sauceId]--;
            }
        } else {
            if (isset($this->tempSauceWingCounts[0][$sauceId]) && $this->tempSauceWingCounts[0][$sauceId] > 0) {
                $this->tempSauceWingCounts[0][$sauceId]--;
            } elseif (isset($this->tempSauceWingCounts[$sauceId]) && $this->tempSauceWingCounts[$sauceId] > 0) {
                $this->tempSauceWingCounts[$sauceId]--;
            }
        }
    }

    public function incrementSauceSide($sauceId, $unitIndex = 0)
    {
        $qty = max(1, (int) $this->tempItemQuantity);
        $portionLimit = $this->getPortionSauceLimit();

        if ($qty > 1) {
            $unitWingsSum = $this->sumSauceCounts($this->tempSauceWingCounts, $unitIndex)
                          + $this->sumSauceCounts($this->tempSauceSideCounts, $unitIndex);

            if ($unitWingsSum < $portionLimit) {
                $this->tempSauceSideCounts[$unitIndex][$sauceId] = ($this->tempSauceSideCounts[$unitIndex][$sauceId] ?? 0) + 1;
            }
        } else {
            $currentWingsSum = $this->sumSauceCounts($this->tempSauceWingCounts)
                             + $this->sumSauceCounts($this->tempSauceSideCounts);
            $totalLimit = $this->tempProductWingsCount > 0 ? $this->tempProductWingsCount : $portionLimit;

            if ($currentWingsSum < $totalLimit) {
                if (isset($this->tempSauceSideCounts[0]) && is_array($this->tempSauceSideCounts[0])) {
                    $this->tempSauceSideCounts[0][$sauceId] = ($this->tempSauceSideCounts[0][$sauceId] ?? 0) + 1;
                } else {
                    $this->tempSauceSideCounts[$sauceId] = ($this->tempSauceSideCounts[$sauceId] ?? 0) + 1;
                }
            }
        }
    }

    public function decrementSauceSide($sauceId, $unitIndex = 0)
    {
        if ($this->tempItemQuantity > 1) {
            if (isset($this->tempSauceSideCounts[$unitIndex][$sauceId]) && $this->tempSauceSideCounts[$unitIndex][$sauceId] > 0) {
                $this->tempSauceSideCounts[$unitIndex][$sauceId]--;
            }
        } else {
            if (isset($this->tempSauceSideCounts[0][$sauceId]) && $this->tempSauceSideCounts[0][$sauceId] > 0) {
                $this->tempSauceSideCounts[0][$sauceId]--;
            } elseif (isset($this->tempSauceSideCounts[$sauceId]) && $this->tempSauceSideCounts[$sauceId] > 0) {
                $this->tempSauceSideCounts[$sauceId]--;
            }
        }
    }

    public function confirmSauces()
    {
        $mappedSauces = [];
        $unitSauces = [];
        $qty = max(1, (int) $this->tempItemQuantity);

        for ($u = 0; $u < $qty; $u++) {
            $unitSauces[$u] = [];
            foreach ($this->tempSelectedSauceIds as $id) {
                $sauce = $this->allSauces->firstWhere('id', $id);
                if ($sauce) {
                    $w = is_array($this->tempSauceWingCounts[$u] ?? null)
                        ? ($this->tempSauceWingCounts[$u][$id] ?? 0)
                        : ($this->tempSauceWingCounts[$id] ?? 0);
                    $s = is_array($this->tempSauceSideCounts[$u] ?? null)
                        ? ($this->tempSauceSideCounts[$u][$id] ?? 0)
                        : ($this->tempSauceSideCounts[$id] ?? 0);

                    if ($w > 0 || $s > 0) {
                        $unitSauces[$u][] = [
                            'id' => $sauce->id,
                            'name' => $sauce->name,
                            'qty' => $w,
                            'qty_side' => $s,
                        ];
                    }
                }
            }
        }

        foreach ($this->tempSelectedSauceIds as $id) {
            $sauce = $this->allSauces->firstWhere('id', $id);
            if ($sauce) {
                $totalWingQty = 0;
                $totalSideQty = 0;

                if ($qty > 1) {
                    for ($u = 0; $u < $qty; $u++) {
                        if (isset($this->tempSauceWingCounts[$u]) && is_array($this->tempSauceWingCounts[$u])) {
                            $totalWingQty += $this->tempSauceWingCounts[$u][$id] ?? 0;
                        } else {
                            $totalWingQty += $this->tempSauceWingCounts[$id] ?? 0;
                        }

                        if (isset($this->tempSauceSideCounts[$u]) && is_array($this->tempSauceSideCounts[$u])) {
                            $totalSideQty += $this->tempSauceSideCounts[$u][$id] ?? 0;
                        } else {
                            $totalSideQty += $this->tempSauceSideCounts[$id] ?? 0;
                        }
                    }
                } else {
                    if (isset($this->tempSauceWingCounts[0]) && is_array($this->tempSauceWingCounts[0])) {
                        $totalWingQty = $this->tempSauceWingCounts[0][$id] ?? 0;
                    } else {
                        $totalWingQty = $this->tempSauceWingCounts[$id] ?? 0;
                    }

                    if (isset($this->tempSauceSideCounts[0]) && is_array($this->tempSauceSideCounts[0])) {
                        $totalSideQty = $this->tempSauceSideCounts[0][$id] ?? 0;
                    } else {
                        $totalSideQty = $this->tempSauceSideCounts[$id] ?? 0;
                    }
                }

                $mappedSauces[] = [
                    'id' => $sauce->id,
                    'name' => $sauce->name,
                    'qty' => $totalWingQty,
                    'qty_side' => $totalSideQty,
                ];
            }
        }
        
        $this->cart[$this->tempCartIndex]['sauces'] = $mappedSauces;
        $this->cart[$this->tempCartIndex]['unit_sauces'] = $unitSauces;
        $this->showSauceModal = false;
        $this->saveCartToSession();
    }

    // --- Totales ---
    public function getSubtotalProperty()
    {
        $branchId = auth()->user()?->activeBranchId() ?? 1;
        $validator = app(\App\Modules\Orders\Services\WingSauceValidator::class);

        $subtotal = collect($this->cart)->sum(function($item) use ($branchId, $validator) {
            $basePrice = $item['price'] * $item['quantity'];

            $extraCharge = 0;
            if (!empty($item['variant_id']) && !empty($item['sauces'])) {
                $variant = \App\Modules\Menu\Models\ProductVariant::find($item['variant_id']);
                if ($variant) {
                    $saucesData = [];
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

                    if (!empty($saucesData)) {
                        try {
                            $extraCharge = $validator->validate($variant, $branchId, $saucesData, (int) ($item['quantity'] ?? 1));
                        } catch (\Throwable $e) {
                            $extraCharge = 0;
                        }
                    }
                }
            }

            return $basePrice + $extraCharge;
        });

        $this->recalculateDiscount();

        return $subtotal;
    }

    public function getTotalProperty()
    {
        return max(0, $this->subtotal - $this->discountAmount);
    }

    public function setBillAmount($amount)
    {
        if ($amount === 'exact') {
            $this->cashReceived = (float) $this->total > 0 ? (string) round($this->total, 2) : '';
        } else {
            $this->cashReceived = (string) $amount;
        }
    }

    public function getCashChangeProperty(): float
    {
        $received = (float) $this->cashReceived;
        $total = (float) $this->total;
        if ($received > 0 && $received >= $total) {
            return round($received - $total, 2);
        }
        return 0.0;
    }

    public function getCashMissingProperty(): float
    {
        $received = (float) $this->cashReceived;
        $total = (float) $this->total;
        if ($received > 0 && $received < $total) {
            return round($total - $received, 2);
        }
        return 0.0;
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
            $this->tableId ? 'dine_in' : $this->orderType,
            $this->customerName
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
        $this->editingOrderId = null;
        $this->cart = [];
        $this->orderNotes = '';
        $this->customerName = '';
        $this->cashReceived = '';
        $this->selectedPromotionId = null;
        $this->selectedPromotionName = '';
        $this->discountAmount = 0;
        $this->promotionWarning = '';
        $this->saveCartToSession();
    }

    #[On('edit-order')]
    public function loadOrderForEditing($orderId)
    {
        $order = \App\Modules\Orders\Models\Order::with([
            'items.productVariant.product',
            'items.sauces.sauce',
            'table'
        ])->find($orderId);

        if (!$order || $order->status !== 'open') {
            $this->dispatch('pos-error', message: 'El pedido no está abierto para edición.');
            return;
        }

        $this->editingOrderId = $order->id;
        $this->tableId = $order->table_id;
        $this->tableName = $order->table ? $order->table->name : null;
        $this->orderType = $order->order_type ?? ($order->table_id ? 'dine_in' : 'takeaway');
        $this->orderNotes = $order->notes ?? '';
        $this->customerName = $order->customer_name ?? '';
        $this->selectedPromotionId = $order->promotion_id;
        if ($order->promotion_id) {
            $promo = \App\Modules\Promotions\Models\Promotion::find($order->promotion_id);
            if ($promo) {
                $this->selectedPromotionName = $promo->name;
            }
        } else {
            $this->selectedPromotionName = '';
        }

        $newCart = [];
        foreach ($order->items as $item) {
            $variant = $item->productVariant;
            if (!$variant || !$variant->product) continue;

            $mappedSauces = [];
            foreach ($item->sauces as $sauceRelation) {
                if (!$sauceRelation->sauce) continue;
                $mappedSauces[] = [
                    'id' => $sauceRelation->sauce_id,
                    'name' => $sauceRelation->sauce->name,
                    'qty' => $sauceRelation->is_coated ? $sauceRelation->quantity : 0,
                    'qty_side' => !$sauceRelation->is_coated ? $sauceRelation->quantity : 0,
                ];
            }

            $consolidatedSauces = [];
            foreach ($mappedSauces as $s) {
                $idx = null;
                foreach ($consolidatedSauces as $k => $c) {
                    if ($c['id'] === $s['id']) {
                        $idx = $k;
                        break;
                    }
                }
                if ($idx !== null) {
                    $consolidatedSauces[$idx]['qty'] += $s['qty'];
                    $consolidatedSauces[$idx]['qty_side'] += $s['qty_side'];
                } else {
                    $consolidatedSauces[] = $s;
                }
            }

            $newCart[] = [
                'id' => 'item_' . $item->id,
                'variant_id' => $item->product_variant_id,
                'variant_name' => $variant->name,
                'product_name' => $variant->product->name,
                'price' => (float) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'notes' => $item->notes ?? '',
                'has_sauces' => (bool) $variant->product->has_sauces,
                'max_sauces' => (int) $variant->max_sauces,
                'wings_count' => (int) $variant->wings_count,
                'sauces' => $consolidatedSauces,
            ];
        }

        $this->cart = $newCart;
        $this->saveCartToSession();
        $this->showUnpaidOrdersModal = false;
        $this->recalculateDiscount();
    }

    public function cancelEditing()
    {
        $this->editingOrderId = null;
        $this->resetCartState();
        $this->dispatch('order-saved', urls: []);
    }

    public function updateOrder()
    {
        if (!$this->editingOrderId) return;

        $order = \App\Modules\Orders\Models\Order::find($this->editingOrderId);
        if (!$order || $order->status !== 'open') {
            $this->dispatch('pos-error', message: 'El pedido no se encuentra disponible para guardar cambios.');
            $this->cancelEditing();
            return;
        }

        if (empty($this->cart)) {
            $this->dispatch('pos-error', message: 'El pedido debe tener al menos un ítem.');
            return;
        }

        $isSuccess = false;
        $orderId = $order->id;

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                $orderService = app(\App\Modules\Orders\Services\OrderService::class);

                // Eliminar ítems previos
                foreach ($order->items()->get() as $oldItem) {
                    $orderService->removeItem($oldItem);
                }

                // Actualizar encabezado
                $order->update([
                    'notes' => $this->orderNotes,
                    'customer_name' => $this->customerName,
                    'order_type' => $this->tableId ? 'dine_in' : $this->orderType,
                ]);

                // Crear nuevos ítems del carrito
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

                // Descontar inventario si aplica
                try {
                    app(\App\Modules\Inventory\Services\InventoryService::class)
                        ->decrementOnSale($order->fresh(['items']));
                } catch (\Throwable $e) {
                    Log::warning('Inventario no descontado en edición: ' . $e->getMessage());
                }

                // Promociones
                if ($this->selectedPromotionId) {
                    try {
                        $promotionEngine = app(\App\Modules\Promotions\Services\PromotionEngine::class);
                        $promotionEngine->apply($order, $this->selectedPromotionId);
                    } catch (\Exception $e) {
                        Log::warning('Promoción no aplicada al editar: ' . $e->getMessage());
                    }
                } else {
                    $order->update([
                        'promotion_id' => null,
                        'discount' => 0,
                    ]);
                    $orderService->recalculateOrder($order);
                }
            });

            $isSuccess = true;
        } catch (\Throwable $e) {
            Log::error('Error actualizando pedido #' . $orderId . ': ' . $e->getMessage());
            $this->dispatch('pos-error', message: 'No se pudo guardar la edición del pedido: ' . $e->getMessage());
            return;
        }

        if ($isSuccess) {
            $this->editingOrderId = null;
            $this->resetCartState();
            session()->flash('message', 'Pedido #' . ($order->daily_number ?? $orderId) . ' actualizado correctamente.');

            $this->dispatch('order-saved', urls: [
                route('pos.tickets.cashier', ['order' => $orderId]),
                route('pos.tickets.kitchen', ['order' => $orderId]),
            ]);
        }
    }

    public function submitOrder()
    {
        if (empty($this->cart)) return;

        // Si la opción elegida es "Comer aquí" pero no se seleccionó mesa aún, solicitar la mesa
        if ($this->orderType === 'dine_in' && !$this->tableId) {
            $this->showTableSelectModal = true;
            return;
        }

        // Todos los pedidos se cobran al momento de pedir ("El cliente paga al pedir")
        if (!$this->cashIsOpen()) {
            return;
        }
        $this->iniciarPagos();
        $this->showPaymentModal = true;
    }

    public function confirmTableSelectAndSubmit($tableId)
    {
        $this->selectTable($tableId);
        $this->submitOrder();
    }

    public function liberateTable($tableId = null)
    {
        $id = $tableId ?? $this->tableId;
        if ($id) {
            \App\Models\Table::where('id', $id)->update(['status' => 'available']);
            if ($this->tableId == $id) {
                $this->tableId = null;
                $this->tableName = null;
            }
        }
    }

    public function occupyTable($tableId = null)
    {
        $id = $tableId ?? $this->tableId;
        if ($id) {
            \App\Models\Table::where('id', $id)->update(['status' => 'occupied']);
        }
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
        $orders = \App\Modules\Orders\Models\Order::where('branch_id', $branchId)
            ->where('status', 'open')
            ->whereNull('table_id')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($orders as $order) {
            if ((float) $order->total <= 0 && $order->items()->count() > 0) {
                app(\App\Modules\Orders\Services\OrderService::class)->recalculateOrder($order);
            }
        }

        $this->unpaidOrders = $orders->fresh();
        $this->showUnpaidOrdersModal = true;
    }

    public function payUnpaidOrder($orderId)
    {
        $order = \App\Modules\Orders\Models\Order::find($orderId);
        if (!$order) return;

        if (!$this->cashIsOpen()) {
            return;
        }

        if ((float) $order->total <= 0 && $order->items()->count() > 0) {
            app(\App\Modules\Orders\Services\OrderService::class)->recalculateOrder($order);
            $order->refresh();
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

    public function updatedCustomerName()
    {
        $this->saveCartToSession();
    }

    // --- Persistencia Sesión ---
    protected function saveCartToSession()
    {
        session()->put('pos_cart', $this->cart);
        session()->put('pos_notes', $this->orderNotes);
        session()->put('pos_customer_name', $this->customerName);
        session()->put('pos_promo_id', $this->selectedPromotionId);
        session()->put('pos_promo_name', $this->selectedPromotionName);
    }

    protected function loadCartFromSession()
    {
        $this->cart = session()->get('pos_cart', []);
        $this->orderNotes = session()->get('pos_notes', '');
        $this->customerName = session()->get('pos_customer_name', '');
        $this->selectedPromotionId = session()->get('pos_promo_id');
        $this->selectedPromotionName = session()->get('pos_promo_name', '');
    }

    public function render()
    {
        return view('livewire.pos.order-builder');
    }
}
