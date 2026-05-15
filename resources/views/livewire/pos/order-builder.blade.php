<div style="height: calc(100vh - 100px); display: flex; gap: 1rem; font-family: 'Inter', sans-serif;">

    <style>
        /* ─── Catálogo Panel ──────────────────────────────────── */
        .catalog-panel {
            flex: 1;
            background: #111;
            border: 1px solid #222;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        /* Categories bar */
        .categories-bar {
            padding: 1rem;
            border-bottom: 1px solid #1e1e1e;
            background: #0d0d0d;
            overflow-x: auto;
            white-space: nowrap;
            display: flex;
            gap: 0.5rem;
        }
        .categories-bar::-webkit-scrollbar { height: 4px; }
        .categories-bar::-webkit-scrollbar-track { background: transparent; }
        .categories-bar::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
        .cat-btn {
            padding: 0.6rem 1.25rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .cat-btn-active {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        }
        .cat-btn-inactive {
            background: #1a1a1a;
            color: #888;
            border: 1px solid #2a2a2a;
        }
        .cat-btn-inactive:hover {
            border-color: #dc2626;
            color: #dc2626;
            background: rgba(220, 38, 38, 0.05);
        }
        /* Products Area */
        .products-area {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem;
            background: #0d0d0d;
        }
        .products-area::-webkit-scrollbar { width: 4px; }
        .products-area::-webkit-scrollbar-track { background: transparent; }
        .products-area::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 1024px) { .products-grid { grid-template-columns: repeat(3, 1fr); } }

        .prod-card {
            cursor: pointer;
            background: #141414;
            padding: 1rem;
            border-radius: 16px;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }
        .prod-card:hover {
            border-color: rgba(220, 38, 38, 0.3);
            transform: translateY(-2px);
        }
        .prod-card-active {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        .prod-thumb {
            height: 80px;
            background: #1a1a1a;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .prod-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }
        .prod-thumb span {
            font-size: 1.75rem;
        }
        .prod-name {
            font-weight: 700;
            color: #e5e5e5;
            font-size: 0.85rem;
            line-height: 1.3;
        }
        .prod-sauce-badge {
            display: inline-block;
            margin-top: 0.5rem;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            background: rgba(249, 115, 22, 0.1);
            color: #f97316;
            border: 1px solid rgba(249, 115, 22, 0.2);
            border-radius: 50px;
        }
        /* Variants section */
        .variants-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.75rem;
        }
        .variants-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }
        .variant-btn {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #141414;
            border: 1px solid #2a2a2a;
            padding: 1rem;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #ccc;
            font-size: 0.85rem;
        }
        .variant-btn:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-color: #dc2626;
            color: #fff;
        }
        .variant-btn:hover .variant-price {
            color: #fff;
        }
        .variant-name {
            font-weight: 700;
        }
        .variant-price {
            font-weight: 900;
            color: #f97316;
            transition: color 0.2s ease;
        }

        /* ─── Ticket/Cart Panel ─────────────────────────────── */
        .ticket-panel {
            width: 380px;
            background: #111;
            border: 1px solid #222;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .ticket-header {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #0a0a0a, #111);
            border-bottom: 1px solid #222;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ticket-title {
            font-weight: 800;
            font-size: 1.05rem;
            color: #fff;
        }
        .ticket-count {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            padding: 0.25rem 0.7rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .ticket-items {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }
        .ticket-items::-webkit-scrollbar { width: 4px; }
        .ticket-items::-webkit-scrollbar-track { background: transparent; }
        .ticket-items::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
        .ticket-item {
            background: #0d0d0d;
            border: 1px solid #1e1e1e;
            border-radius: 14px;
            padding: 0.85rem;
            margin-bottom: 0.75rem;
        }
        .ticket-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }
        .ticket-item-name {
            font-weight: 700;
            color: #e5e5e5;
            font-size: 0.85rem;
            line-height: 1;
        }
        .ticket-item-variant {
            font-size: 0.7rem;
            color: #555;
            margin-top: 0.15rem;
        }
        .ticket-item-price {
            font-weight: 900;
            color: #f97316;
            font-size: 0.9rem;
        }
        .ticket-sauce-btn {
            font-size: 0.7rem;
            font-weight: 700;
            color: #dc2626;
            background: rgba(220, 38, 38, 0.08);
            border: 1px solid rgba(220, 38, 38, 0.15);
            padding: 0.2rem 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .ticket-sauce-btn:hover {
            background: rgba(220, 38, 38, 0.15);
        }
        .ticket-sauce-tag {
            font-size: 0.6rem;
            background: #1a1a1a;
            color: #666;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
        }
        .ticket-item-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-top: 0.5rem;
        }
        .ticket-note-input {
            flex: 1;
            display: flex;
            align-items: center;
            background: #141414;
            border: 1px solid #222;
            border-radius: 8px;
            padding: 0 0.5rem;
            height: 32px;
        }
        .ticket-note-input span { color: #444; font-size: 0.7rem; margin-right: 0.25rem; }
        .ticket-note-input input {
            width: 100%;
            font-size: 0.75rem;
            background: transparent;
            border: none;
            color: #ccc;
            outline: none;
        }
        .qty-controls {
            display: flex;
            background: #141414;
            border: 1px solid #222;
            border-radius: 8px;
            overflow: hidden;
            height: 32px;
        }
        .qty-btn {
            width: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            background: transparent;
            border: none;
            color: #666;
        }
        .qty-btn:first-child:hover { background: rgba(220, 38, 38, 0.1); color: #dc2626; }
        .qty-btn:last-child:hover { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .qty-value {
            padding: 0 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.8rem;
            color: #fff;
            border-left: 1px solid #222;
            border-right: 1px solid #222;
        }
        /* Empty state */
        .ticket-empty {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        .ticket-empty span { font-size: 3rem; margin-bottom: 0.75rem; opacity: 0.3; }
        .ticket-empty p { font-weight: 500; color: #444; font-size: 0.85rem; }
        /* Footer */
        .ticket-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #222;
            background: #0d0d0d;
        }
        .ticket-notes-area {
            width: 100%;
            border-radius: 12px;
            border: 1px solid #222;
            background: #111;
            color: #ccc;
            font-size: 0.8rem;
            padding: 0.65rem;
            margin-bottom: 1rem;
            resize: vertical;
            outline: none;
            font-family: inherit;
        }
        .ticket-notes-area:focus {
            border-color: #dc2626;
        }
        .ticket-total-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 1rem;
        }
        .ticket-total-label {
            color: #555;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.08em;
        }
        .ticket-total-value {
            font-size: 1.75rem;
            font-weight: 900;
            background: linear-gradient(135deg, #f97316, #dc2626);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn-send-kitchen {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            font-weight: 800;
            font-size: 0.9rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 16px rgba(220, 38, 38, 0.2);
        }
        .btn-send-kitchen:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(220, 38, 38, 0.3);
        }
        .btn-send-kitchen:active {
            transform: scale(0.97);
        }
        /* ─── Promo Section ──────────────────────────────────── */
        .promo-section {
            margin-bottom: 0.75rem;
        }
        .btn-add-promo {
            width: 100%; padding: 0.6rem; background: rgba(139, 92, 246, 0.08);
            border: 1px dashed rgba(139, 92, 246, 0.3); border-radius: 12px;
            color: #a78bfa; font-weight: 700; font-size: 0.8rem; cursor: pointer;
            transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        }
        .btn-add-promo:hover {
            background: rgba(139, 92, 246, 0.12); border-color: #a78bfa;
        }
        .promo-applied {
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 12px; padding: 0.6rem 0.85rem;
        }
        .promo-applied-info { display: flex; align-items: center; gap: 0.4rem; }
        .promo-applied-name { font-size: 0.75rem; font-weight: 700; color: #a78bfa; }
        .promo-applied-remove {
            background: transparent; border: 1px solid rgba(220, 38, 38, 0.3); color: #f87171;
            width: 24px; height: 24px; border-radius: 6px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 0.7rem;
            transition: all 0.2s;
        }
        .promo-applied-remove:hover { background: rgba(220, 38, 38, 0.1); border-color: #dc2626; }
        .promo-discount-row {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 0.4rem; padding: 0 0.15rem;
        }
        .promo-discount-label { color: #a78bfa; font-size: 0.75rem; font-weight: 600; }
        .promo-discount-value { color: #a78bfa; font-size: 0.9rem; font-weight: 800; }

        /* Promo Modal */
        .promo-modal-overlay {
            position: fixed; inset: 0; z-index: 50; display: flex; align-items: center;
            justify-content: center; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px);
        }
        .promo-modal {
            background: #111; border: 1px solid #222; width: 100%; max-width: 440px;
            border-radius: 20px; overflow: hidden; max-height: 80vh; display: flex; flex-direction: column;
        }
        .promo-modal-header {
            padding: 1.25rem 1.5rem; background: #0a0a0a; border-bottom: 1px solid #222;
            display: flex; justify-content: space-between; align-items: center;
        }
        .promo-modal-header h3 { font-weight: 800; color: #fff; font-size: 1.1rem; }
        .promo-modal-body { padding: 1rem 1.25rem; overflow-y: auto; flex: 1; }
        .promo-option {
            background: #0d0d0d; border: 1px solid #1e1e1e; border-radius: 14px;
            padding: 1rem; margin-bottom: 0.65rem; cursor: pointer; transition: all 0.2s;
        }
        .promo-option:hover { border-color: #a78bfa; background: rgba(139, 92, 246, 0.03); transform: translateY(-1px); }
        .promo-option-name { font-weight: 800; color: #e5e5e5; font-size: 0.9rem; margin-bottom: 0.3rem; }
        .promo-option-desc { font-size: 0.75rem; color: #666; margin-bottom: 0.4rem; }
        .promo-option-tags { display: flex; flex-wrap: wrap; gap: 0.35rem; }
        .promo-option-tag {
            font-size: 0.6rem; font-weight: 700; padding: 0.15rem 0.45rem;
            border-radius: 50px; text-transform: uppercase;
        }
        .promo-option-tag-type { background: rgba(139, 92, 246, 0.1); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.2); }
        .promo-option-tag-value { background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.2); }
        .promo-option-tag-branch { background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2); }
        .promo-empty-list { text-align: center; padding: 2rem; color: #444; }
        .promo-empty-list span { font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3; }

        /* ─── Sauce Modal ───────────────────────────────────── */
        .sauce-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(8px);
        }
        .sauce-modal {
            background: #111;
            border: 1px solid #222;
            width: 100%;
            max-width: 440px;
            border-radius: 20px;
            overflow: hidden;
        }
        .sauce-modal-header {
            padding: 1.25rem 1.5rem;
            background: #0a0a0a;
            border-bottom: 1px solid #222;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sauce-modal-header h3 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
        }
        .sauce-modal-header p {
            font-size: 0.8rem;
            color: #f97316;
            margin-top: 0.15rem;
        }
        .sauce-modal-close {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid #222;
            border-radius: 10px;
            color: #666;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .sauce-modal-close:hover {
            color: #dc2626;
            border-color: #dc2626;
        }
        .sauce-modal-body {
            padding: 1.25rem 1.5rem;
            max-height: 380px;
            overflow-y: auto;
        }
        .sauce-modal-body::-webkit-scrollbar { width: 4px; }
        .sauce-modal-body::-webkit-scrollbar-track { background: transparent; }
        .sauce-modal-body::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
        .sauce-progress {
            width: 100%;
            background: #1a1a1a;
            border-radius: 50px;
            height: 6px;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .sauce-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #dc2626, #f97316);
            border-radius: 50px;
            transition: width 0.3s ease;
        }
        .sauce-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.85rem;
            background: #0d0d0d;
            border: 1px solid #1e1e1e;
            border-radius: 14px;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
        }
        .sauce-row-active {
            border-color: #dc2626;
            background: rgba(220, 38, 38, 0.05);
        }
        .sauce-name {
            font-weight: 700;
            color: #e5e5e5;
            font-size: 0.85rem;
        }
        .sauce-spice { margin-top: 0.2rem; }
        .sauce-spice span { font-size: 0.6rem; }
        .sauce-counter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #111;
            border: 1px solid #222;
            padding: 0.2rem;
            border-radius: 10px;
        }
        .sauce-counter-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #666;
            transition: all 0.15s ease;
        }
        .sauce-counter-btn:hover {
            background: #1a1a1a;
            color: #fff;
        }
        .sauce-counter-value {
            font-weight: 900;
            font-size: 0.85rem;
            width: 20px;
            text-align: center;
            color: #fff;
        }
        .sauce-modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid #222;
            background: #0d0d0d;
        }
        .btn-confirm-sauces {
            width: 100%;
            padding: 0.85rem;
            border-radius: 14px;
            font-weight: 800;
            font-size: 0.85rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-confirm-sauces-ready {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .btn-confirm-sauces-ready:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        .btn-confirm-sauces-disabled {
            background: #1a1a1a;
            color: #444;
            cursor: not-allowed;
            display: none;
        }
        .sauce-missing-text {
            text-align: center;
            color: #555;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>

    <!-- Catálogo Menu (Izquierda 60%) -->
    <div class="catalog-panel">
        
        <!-- Categorías -->
        <div class="categories-bar">
            @foreach($categories as $category)
                <button wire:click="selectCategory({{ $category->id }})" 
                        class="cat-btn {{ $activeCategoryId === $category->id ? 'cat-btn-active' : 'cat-btn-inactive' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        <!-- Productos y Variantes -->
        <div class="products-area">
            @if($products)
                <div class="products-grid">
                    @foreach($products as $product)
                        <div wire:click="selectProduct({{ $product->id }})" 
                             class="prod-card {{ $activeProductId === $product->id ? 'prod-card-active' : '' }}">
                            <div class="prod-thumb">
                                @if($product->image) <img src="{{ $product->image }}" alt="{{ $product->name }}"> 
                                @else <span>🍗</span> @endif
                            </div>
                            <h3 class="prod-name">{{ $product->name }}</h3>
                            @if($product->has_sauces)
                                <span class="prod-sauce-badge">Personaliza tus salsas</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Variantes del Producto Seleccionado -->
            @if($activeProductId && count($variants) > 0)
                <h4 class="variants-title">Selecciona una variante:</h4>
                <div class="variants-grid">
                    @foreach($variants as $variant)
                        <button wire:click="addVariant({{ $variant->id }})" class="variant-btn">
                            <span class="variant-name">{{ $variant->name }}</span>
                            <span class="variant-price">${{ number_format($variant->price, 2) }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Ticket/Carrito (Derecha 40%) -->
    <div class="ticket-panel">
        <div class="ticket-header">
            <h2 class="ticket-title">Ticket Mesa #{{ $tableId ?? '...' }}</h2>
            <span class="ticket-count">{{ count($cart) }} Items</span>
        </div>

        <div class="ticket-items" style="{{ count($cart) === 0 ? '' : '' }}">
            @forelse($cart as $index => $item)
                <div class="ticket-item">
                    <div class="ticket-item-header">
                        <div>
                            <h4 class="ticket-item-name">{{ $item['product_name'] }}</h4>
                            <span class="ticket-item-variant">{{ $item['variant_name'] }}</span>
                        </div>
                        <span class="ticket-item-price">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </div>

                    @if($item['has_sauces'])
                        <div style="margin-bottom: 0.5rem;">
                            <button wire:click="openSauceModal({{ $index }})" class="ticket-sauce-btn">
                                ✏️ Configurar Salsas
                            </button>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; margin-top: 0.35rem;">
                                @foreach($item['sauces'] as $sauce)
                                    <span class="ticket-sauce-tag">{{ $sauce['name'] }} x{{ $sauce['qty'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="ticket-item-controls">
                        <!-- Input Nota Item -->
                        <div class="ticket-note-input">
                            <span>✏️</span>
                            <input wire:model.live.debounce.500ms="cart.{{ $index }}.notes" type="text" placeholder="Nota ítem...">
                        </div>
                        <!-- Controles QTY -->
                        <div class="qty-controls">
                            <button wire:click="decrementQty({{ $index }})" class="qty-btn">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </button>
                            <span class="qty-value">{{ $item['quantity'] }}</span>
                            <button wire:click="incrementQty({{ $index }})" class="qty-btn">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="ticket-empty">
                    <span>🛒</span>
                    <p>El pedido está vacío</p>
                </div>
            @endforelse
        </div>

        <div class="ticket-footer">
            {{-- Sección de Promociones --}}
            <div class="promo-section">
                @if($selectedPromotionId)
                    <div class="promo-applied">
                        <div class="promo-applied-info">
                            <span>🎉</span>
                            <span class="promo-applied-name">{{ $selectedPromotionName }}</span>
                        </div>
                        <button wire:click="removePromotion" class="promo-applied-remove" title="Quitar promoción">✕</button>
                    </div>
                @else
                    <button wire:click="openPromoModal" class="btn-add-promo">
                        🎉 Aplicar Promoción
                    </button>
                @endif
            </div>

            {{-- Subtotal --}}
            @if($discountAmount > 0)
                <div class="promo-discount-row">
                    <span class="ticket-total-label">Subtotal</span>
                    <span style="font-size: 0.9rem; font-weight: 700; color: #888;">${{ number_format($this->subtotal, 2) }}</span>
                </div>
                <div class="promo-discount-row">
                    <span class="promo-discount-label">🎉 Descuento</span>
                    <span class="promo-discount-value">-${{ number_format($discountAmount, 2) }}</span>
                </div>
            @endif

            <div class="ticket-total-row">
                <span class="ticket-total-label">Total a Pagar</span>
                <span class="ticket-total-value">${{ number_format($this->total, 2) }}</span>
            </div>
            
            <button wire:click="submitOrder" class="btn-send-kitchen">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                ENVIAR A COCINA
            </button>
        </div>
    </div>

    <!-- Script de Ticket POS -->
    <script>
        function printTicket(url) {
            let printWindow = window.open(url, "PrintTicket", "width=400,height=600");
            if(printWindow) {
                printWindow.focus();
            }
        }
    </script>

    <!-- Modal de Salsas Drawer/Overlay -->
    @if($showSauceModal)
    <div class="sauce-modal-overlay">
        <div class="sauce-modal">
            <div class="sauce-modal-header">
                <div>
                    <h3>Seleccionar Salsas</h3>
                    <p>Elige hasta {{ $tempProductMaxSauces }} salsas (Llevas: {{ $this->getTempSaucesTotal() }})</p>
                </div>
                <button wire:click="$set('showSauceModal', false)" class="sauce-modal-close">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="sauce-modal-body">
                <!-- Barra de Progreso Salsas -->
                <div class="sauce-progress">
                  <div class="sauce-progress-bar" style="width: {{ $tempProductMaxSauces > 0 ? ($this->getTempSaucesTotal() / $tempProductMaxSauces) * 100 : 0 }}%"></div>
                </div>

                @foreach($allSauces as $sauce)
                    <div wire:key="sauce-{{ $sauce->id }}" class="sauce-row {{ isset($tempSelectedSauces[$sauce->id]) && $tempSelectedSauces[$sauce->id] > 0 ? 'sauce-row-active' : '' }}">
                        <div>
                            <h4 class="sauce-name">{{ $sauce->name }}</h4>
                            <div class="sauce-spice">
                                @for($i = 0; $i < $sauce->spice_level; $i++)
                                    <span>🌶️</span>
                                @endfor
                            </div>
                        </div>
                        
                        <div class="sauce-counter">
                            <button wire:click="decrementSauce({{ $sauce->id }})" class="sauce-counter-btn" style="{{ empty($tempSelectedSauces[$sauce->id]) ? 'opacity:0.3;cursor:not-allowed;' : '' }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </button>
                            <span class="sauce-counter-value">{{ $tempSelectedSauces[$sauce->id] ?? 0 }}</span>
                            <button wire:click="incrementSauce({{ $sauce->id }})" class="sauce-counter-btn" style="{{ $this->getTempSaucesTotal() >= $tempProductMaxSauces ? 'opacity:0.3;cursor:not-allowed;' : '' }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="sauce-modal-footer">
                <button wire:click="confirmSauces" class="btn-confirm-sauces {{ $this->getTempSaucesTotal() == $tempProductMaxSauces ? 'btn-confirm-sauces-ready' : 'btn-confirm-sauces-disabled' }}">
                    CONFIRMAR {{ $this->getTempSaucesTotal() }}/{{ $tempProductMaxSauces }} SALSAS
                </button>
                @if($this->getTempSaucesTotal() < $tempProductMaxSauces)
                    <p class="sauce-missing-text" style="margin-top:0.5rem;">Te faltan seleccionar {{ $tempProductMaxSauces - $this->getTempSaucesTotal() }} salsas</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de Selección de Promociones --}}
    @if($showPromoModal)
    <div class="promo-modal-overlay">
        <div class="promo-modal">
            <div class="promo-modal-header">
                <h3>🎉 Seleccionar Promoción</h3>
                <button wire:click="$set('showPromoModal', false)" style="background:transparent;border:1px solid #222;color:#666;width:36px;height:36px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    ✕
                </button>
            </div>
            <div class="promo-modal-body">
                @if(count($availablePromotions) > 0)
                    @foreach($availablePromotions as $promo)
                        <div wire:click="selectPromotion({{ data_get($promo, 'id') }})" class="promo-option">
                            <div class="promo-option-name">{{ data_get($promo, 'name') }}</div>
                            @if(data_get($promo, 'description'))
                                <div class="promo-option-desc">{{ data_get($promo, 'description') }}</div>
                            @endif
                            <div class="promo-option-tags">
                                <span class="promo-option-tag promo-option-tag-type">
                                    @switch(data_get($promo, 'type'))
                                        @case('discount') 💰 Descuento @break
                                        @case('combo') 📦 Combo @break
                                        @case('birthday') 🎂 Cumpleaños @break
                                        @case('free_item') 🎁 Gratis @break
                                        @case('custom') ⚙️ Especial @break
                                    @endswitch
                                </span>
                                <span class="promo-option-tag promo-option-tag-value">
                                    @if(data_get($promo, 'discount_type') === 'percentage')
                                        {{ data_get($promo, 'discount_value') }}% OFF
                                    @elseif(data_get($promo, 'discount_type') === 'fixed')
                                        -${{ number_format(data_get($promo, 'discount_value'), 2) }}
                                    @else
                                        Gratis x{{ data_get($promo, 'free_quantity') }}
                                    @endif
                                </span>
                                <span class="promo-option-tag promo-option-tag-branch">
                                    {{ data_get($promo, 'branch.name', '🌐 Todas') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="promo-empty-list">
                        <span>🎉</span>
                        <p>No hay promociones activas disponibles.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
