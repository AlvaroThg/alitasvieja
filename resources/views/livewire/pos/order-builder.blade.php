<div class="pos-order-builder-layout">

    <style>
        .pos-order-builder-layout {
            height: calc(100vh - 100px);
            min-height: 560px;
            display: flex;
            gap: 1rem;
            width: 100%;
            font-family: 'Inter', sans-serif;
        }
        /* ─── Catálogo Panel ──────────────────────────────────── */
        .catalog-panel {
            flex: 1 1 0%;
            min-width: 0;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        /* Categories bar */
        .categories-bar {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            background: var(--bg-base);
            overflow-x: auto;
            white-space: nowrap;
            display: flex;
            gap: 0.5rem;
        }
        .categories-bar::-webkit-scrollbar { height: 4px; }
        .categories-bar::-webkit-scrollbar-track { background: transparent; }
        .categories-bar::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 4px; }
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
            color: var(--text-strong);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        }
        .cat-btn-inactive {
            background: var(--bg-elevated);
            color: var(--text-muted);
            border: 1px solid var(--border);
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
            background: var(--bg-base);
        }
        .products-area::-webkit-scrollbar { width: 4px; }
        .products-area::-webkit-scrollbar-track { background: transparent; }
        .products-area::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 4px; }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .prod-card {
            cursor: pointer;
            background: var(--bg-surface);
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
            background: var(--bg-elevated);
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
            color: var(--text);
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
            color: var(--text-faint);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.75rem;
        }
        .variants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.5rem;
        }
        .variant-btn {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            padding: 1rem;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        .variant-btn:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-color: #dc2626;
            color: var(--text-strong);
        }
        .variant-btn:hover .variant-price {
            color: var(--text-strong);
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
            min-width: 320px;
            max-width: 400px;
            flex-shrink: 0;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        @media (max-width: 900px) {
            .pos-order-builder-layout {
                flex-direction: column;
                height: auto;
                min-height: 0;
            }
            .catalog-panel {
                height: 520px;
            }
            .ticket-panel {
                width: 100%;
                max-width: 100%;
                height: 480px;
            }
        }
        .ticket-header {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, var(--bg-base), var(--bg-surface));
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ticket-title {
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--text-strong);
        }
        .ticket-count {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: var(--text-strong);
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
        .ticket-items::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 4px; }
        .ticket-item {
            background: var(--bg-base);
            border: 1px solid var(--border);
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
            color: var(--text);
            font-size: 0.85rem;
            line-height: 1;
        }
        .ticket-item-variant {
            font-size: 0.7rem;
            color: var(--text-faint);
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
            background: var(--bg-elevated);
            color: var(--text-muted);
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
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0 0.5rem;
            height: 32px;
        }
        .ticket-note-input span { color: var(--text-faint); font-size: 0.7rem; margin-right: 0.25rem; }
        .ticket-note-input input {
            width: 100%;
            font-size: 0.75rem;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            outline: none;
        }
        .qty-controls {
            display: flex;
            background: var(--bg-surface);
            border: 1px solid var(--border);
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
            color: var(--text-muted);
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
            color: var(--text-strong);
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
        }
        /* Empty state */
        .ticket-empty {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--border-strong);
        }
        .ticket-empty span { font-size: 3rem; margin-bottom: 0.75rem; opacity: 0.3; }
        .ticket-empty p { font-weight: 500; color: var(--text-faint); font-size: 0.85rem; }
        /* Footer */
        .ticket-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border);
            background: var(--bg-base);
        }
        .ticket-notes-area {
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--bg-surface);
            color: var(--text-secondary);
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
            color: var(--text-faint);
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
            color: var(--text-strong);
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
            background: var(--bg-surface); border: 1px solid var(--border); width: 100%; max-width: 440px;
            border-radius: 20px; overflow: hidden; max-height: 80vh; display: flex; flex-direction: column;
        }
        .promo-modal-header {
            padding: 1.25rem 1.5rem; background: var(--bg-base); border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .promo-modal-header h3 { font-weight: 800; color: var(--text-strong); font-size: 1.1rem; }
        .promo-modal-body { padding: 1rem 1.25rem; overflow-y: auto; flex: 1; }
        .promo-option {
            background: var(--bg-base); border: 1px solid var(--border); border-radius: 14px;
            padding: 1rem; margin-bottom: 0.65rem; cursor: pointer; transition: all 0.2s;
        }
        .promo-option:hover { border-color: #a78bfa; background: rgba(139, 92, 246, 0.03); transform: translateY(-1px); }
        .promo-option-name { font-weight: 800; color: var(--text); font-size: 0.9rem; margin-bottom: 0.3rem; }
        .promo-option-desc { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.4rem; }
        .promo-option-tags { display: flex; flex-wrap: wrap; gap: 0.35rem; }
        .promo-option-tag {
            font-size: 0.6rem; font-weight: 700; padding: 0.15rem 0.45rem;
            border-radius: 50px; text-transform: uppercase;
        }
        .promo-option-tag-type { background: rgba(139, 92, 246, 0.1); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.2); }
        .promo-option-tag-value { background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.2); }
        .promo-option-tag-branch { background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2); }
        .promo-empty-list { text-align: center; padding: 2rem; color: var(--text-faint); }
        .promo-empty-list span { font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3; }

        /* ─── Sauce Modal ───────────────────────────────────── */
        .sauce-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(8px);
            padding: 0.75rem;
        }
        .sauce-modal {
            background: var(--bg-surface);
            border: 1px solid var(--border-strong);
            width: 98vw;
            height: 96vh;
            max-width: 1400px;
            max-height: 96vh;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        }
        .sauce-modal-header {
            padding: 1.25rem 1.75rem;
            background: var(--bg-base);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sauce-modal-header h3 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-strong);
        }
        .sauce-modal-header p {
            font-size: 0.95rem;
            color: #f97316;
            margin-top: 0.2rem;
            font-weight: 700;
        }
        .sauce-modal-close {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .sauce-modal-close:hover {
            color: #dc2626;
            border-color: #dc2626;
            background: rgba(220, 38, 38, 0.1);
        }
        .sauce-modal-body {
            padding: 1.5rem 1.75rem;
            flex: 1;
            overflow-y: auto;
        }
        .sauce-modal-body::-webkit-scrollbar { width: 6px; }
        .sauce-modal-body::-webkit-scrollbar-track { background: transparent; }
        .sauce-modal-body::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 4px; }
        .sauce-progress {
            width: 100%;
            background: var(--bg-elevated);
            border-radius: 50px;
            height: 8px;
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
            padding: 1.1rem 1.25rem;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: 16px;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }
        .sauce-row-active {
            border-color: #dc2626;
            background: rgba(220, 38, 38, 0.08);
        }
        .sauce-name {
            font-weight: 800;
            color: var(--text-strong);
            font-size: 1.1rem;
        }
        .sauce-spice { margin-top: 0.35rem; display: flex; flex-direction: row; flex-wrap: wrap; gap: 3px; align-items: center; }
        .sauce-spice span { font-size: 0.7rem; }
        .sauce-spice svg { display: block; flex-shrink: 0; }
        .sauce-counter {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            padding: 0.3rem 0.5rem;
            border-radius: 12px;
        }
        .sauce-counter-btn {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: var(--bg-base);
            border: 1px solid var(--border-strong);
            cursor: pointer;
            color: var(--text-strong);
            font-size: 1.1rem;
            font-weight: 800;
            transition: all 0.15s ease;
        }
        .sauce-counter-btn:hover {
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
            border-color: #dc2626;
        }
        .sauce-counter-value {
            font-weight: 900;
            font-size: 1.1rem;
            width: 28px;
            text-align: center;
            color: var(--text-strong);
        }
        .sauce-modal-footer {
            padding: 1.25rem 1.75rem;
            border-top: 1px solid var(--border);
            background: var(--bg-base);
        }
        .btn-confirm-sauces {
            width: 100%;
            padding: 1rem;
            border-radius: 14px;
            font-weight: 900;
            font-size: 1.1rem;
            letter-spacing: 0.03em;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-confirm-sauces-ready {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .btn-confirm-sauces-ready:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        .btn-confirm-sauces-disabled {
            background: var(--bg-elevated);
            color: var(--text-faint);
            cursor: not-allowed;
            display: none;
        }
        .sauce-missing-text {
            text-align: center;
            color: var(--text-faint);
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
                                @else <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="opacity:0.35;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> @endif
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
                        @php $st = $this->availableStock($variant->id); @endphp
                        <button wire:click="addVariant({{ $variant->id }})" class="variant-btn" @if($st !== null && $st <= 0) disabled style="opacity:0.55; cursor:not-allowed;" @endif>
                            <span class="variant-name">
                                {{ $variant->name }}
                                @if($st !== null)
                                    <span style="display:block; font-size:0.65rem; font-weight:700; margin-top:2px; color: {{ $st <= 0 ? '#ef4444' : ($st <= 3 ? '#f97316' : 'var(--text-muted)') }};">
                                        {{ $st <= 0 ? 'Agotado' : 'Quedan: '.$st }}
                                    </span>
                                @endif
                            </span>
                            <span class="variant-price">Bs. {{ number_format($this->priceFor($variant), 2) }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Ticket/Carrito (Derecha 40%) -->
    <div class="ticket-panel">
        <div class="ticket-header" style="flex-direction: column; align-items: stretch; gap: 0.65rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="ticket-title">
                    {{ $tableId ? 'Ticket — '.$tableName : ($orderType === 'delivery' ? 'Ticket — Delivery' : ($orderType === 'takeaway' ? 'Ticket — Para Llevar' : 'Ticket de Venta')) }}
                </h2>
                <span class="ticket-count">{{ count($cart) }} Items</span>
            </div>
            
            {{-- Selector de Tipo de Pedido (Comer aquí / Para llevar / Delivery) --}}
            <div style="display: flex; background: var(--bg-base); border-radius: 10px; padding: 0.25rem; border: 1px solid var(--border); gap: 0.2rem;">
                <button type="button" wire:click="selectOrderType('dine_in')"
                        style="flex: 1; padding: 0.45rem 0.3rem; font-size: 0.73rem; font-weight: 800; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; background: {{ $orderType === 'dine_in' ? 'linear-gradient(135deg, #dc2626, #b91c1c)' : 'transparent' }}; color: {{ $orderType === 'dine_in' ? '#fff' : 'var(--text-muted)' }}; box-shadow: {{ $orderType === 'dine_in' ? '0 2px 6px rgba(220,38,38,0.3)' : 'none' }}; display: flex; align-items: center; justify-content: center; gap: 0.25rem;">
                    <span>🛋️</span> Comer aquí
                </button>
                <button type="button" wire:click="selectOrderType('takeaway')"
                        style="flex: 1; padding: 0.45rem 0.3rem; font-size: 0.73rem; font-weight: 800; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; background: {{ $orderType === 'takeaway' ? 'linear-gradient(135deg, #f97316, #ea580c)' : 'transparent' }}; color: {{ $orderType === 'takeaway' ? '#fff' : 'var(--text-muted)' }}; box-shadow: {{ $orderType === 'takeaway' ? '0 2px 6px rgba(249,115,22,0.3)' : 'none' }}; display: flex; align-items: center; justify-content: center; gap: 0.25rem;">
                    <span>🥡</span> Llevar
                </button>
                <button type="button" wire:click="selectOrderType('delivery')"
                        style="flex: 1; padding: 0.45rem 0.3rem; font-size: 0.73rem; font-weight: 800; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; background: {{ $orderType === 'delivery' ? 'linear-gradient(135deg, #3b82f6, #2563eb)' : 'transparent' }}; color: {{ $orderType === 'delivery' ? '#fff' : 'var(--text-muted)' }}; box-shadow: {{ $orderType === 'delivery' ? '0 2px 6px rgba(59,130,246,0.3)' : 'none' }}; display: flex; align-items: center; justify-content: center; gap: 0.25rem;">
                    <span>🛵</span> Delivery
                </button>
            </div>

            {{-- Selector de Mesa cuando se elige "Comer aquí" --}}
            @if($orderType === 'dine_in')
                <div style="display: flex; align-items: center; gap: 0.4rem; background: rgba(220, 38, 38, 0.05); border: 1px solid rgba(220, 38, 38, 0.2); padding: 0.4rem 0.6rem; border-radius: 10px;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #dc2626; white-space: nowrap;">Mesa:</span>
                    <select wire:change="selectTable($event.target.value)" style="flex: 1; background: var(--bg-surface); border: 1px solid var(--border); color: var(--text-strong); font-size: 0.8rem; font-weight: 700; padding: 0.3rem 0.5rem; border-radius: 8px; outline: none; font-family: inherit;">
                        <option value="">-- Elegir Mesa --</option>
                        @foreach($this->availableTables as $tbl)
                            <option value="{{ $tbl->id }}" {{ $tableId == $tbl->id ? 'selected' : '' }}>
                                {{ $tbl->name }} {{ $tbl->status === 'occupied' ? '(Ocupada)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if($tableId)
                        @php $currentTbl = $this->availableTables->firstWhere('id', $tableId); @endphp
                        @if($currentTbl && $currentTbl->status === 'occupied')
                            <button type="button" wire:click="liberateTable({{ $tableId }})" title="Liberar esta mesa (marcar disponible)"
                                    style="padding: 0.3rem 0.5rem; font-size: 0.7rem; font-weight: 700; background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 6px; cursor: pointer; white-space: nowrap;">
                                🟢 Liberar
                            </button>
                        @elseif($currentTbl)
                            <button type="button" wire:click="occupyTable({{ $tableId }})" title="Ocupar esta mesa"
                                    style="padding: 0.3rem 0.5rem; font-size: 0.7rem; font-weight: 700; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer; white-space: nowrap;">
                                🔴 Ocupar
                            </button>
                        @endif
                    @endif
                </div>
            @endif
        </div>

        @if($editingOrderId)
            <div style="background: rgba(249, 115, 22, 0.15); border: 1px solid rgba(249, 115, 22, 0.4); border-radius: 12px; padding: 0.65rem 0.85rem; margin: 0.75rem 1rem 0; display: flex; align-items: center; justify-content: space-between;">
                <div style="color: #f97316; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Editando Pedido #{{ $editingOrderId }} {{ $tableName ? '('.$tableName.')' : '(Para llevar)' }}
                </div>
                <button wire:click="cancelEditing" style="background: transparent; border: 1px solid rgba(249, 115, 22, 0.4); color: #f97316; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
            </div>
        @endif

        <div class="ticket-items" style="{{ count($cart) === 0 ? '' : '' }}">
            @forelse($cart as $index => $item)
                <div class="ticket-item">
                    <div class="ticket-item-header">
                        <div>
                            <h4 class="ticket-item-name">{{ $item['product_name'] }}</h4>
                            <span class="ticket-item-variant">{{ $item['variant_name'] }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="ticket-item-price">Bs. {{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                            <button wire:click="removeItem({{ $index }})" title="Quitar del pedido"
                                    style="background: transparent; border: none; color: #ef4444; cursor: pointer; padding: 2px; display: flex; align-items: center;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    @if($item['has_sauces'])
                        <div style="margin-bottom: 0.5rem;">
                            @if(empty($item['sauces']))
                                <button wire:click="openSauceModal({{ $index }})" class="ticket-sauce-btn" style="background: rgba(249, 115, 22, 0.12); color: #ea580c; border: 1px solid rgba(249, 115, 22, 0.35); font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                                    🌶️ Elegir Salsas
                                </button>
                            @else
                                <button wire:click="openSauceModal({{ $index }})" class="ticket-sauce-btn" style="background: rgba(34, 197, 94, 0.1); color: #16a34a; border: 1px solid rgba(34, 197, 94, 0.3); font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                                    ✓ Salsas (Editar)
                                </button>
                            @endif
                            <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; margin-top: 0.35rem;">
                                @foreach($item['sauces'] as $sauce)
                                    @php
                                        $str = [];
                                        if (($sauce['qty'] ?? 0) > 0) {
                                            $str[] = ($item['wings_count'] ?? 0) > 0 ? $sauce['qty'] . ' bañadas' : 'bañada';
                                        }
                                        if (($sauce['qty_side'] ?? 0) > 0) {
                                            $str[] = ($item['wings_count'] ?? 0) > 0 ? $sauce['qty_side'] . ' aparte' : 'aparte';
                                        }
                                        $sName = is_array($sauce['name'] ?? null) ? implode(', ', $sauce['name']) : ($sauce['name'] ?? 'Salsa');
                                    @endphp
                                    <span class="ticket-sauce-tag">{{ $sName }}{{ count($str) > 0 ? ' · ' . implode(', ', $str) : '' }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="ticket-item-controls">
                        <!-- Input Nota Item -->
                        <div class="ticket-note-input">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
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
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p>El pedido está vacío</p>
                </div>
            @endforelse
        </div>

        <div class="ticket-footer">
            {{-- Nombre del Cliente / Datos de entrega --}}
            <div style="margin-bottom: 0.75rem;">
                <input wire:model.live.debounce.300ms="customerName" type="text" placeholder="Cliente / Entregar a (Nombre o dato)..."
                       style="width: 100%; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-strong); font-size: 0.85rem; padding: 0.6rem 0.8rem; font-weight: 600; outline: none;">
            </div>

            {{-- Sección de Promociones --}}
            <div class="promo-section">
                @if($selectedPromotionId)
                    <div class="promo-applied">
                        <div class="promo-applied-info">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            <span class="promo-applied-name">{{ $selectedPromotionName }}</span>
                        </div>
                        <button wire:click="removePromotion" class="promo-applied-remove" title="Quitar promoción">✕</button>
                    </div>
                @else
                    <button wire:click="openPromoModal" class="btn-add-promo">
                        Aplicar Promoción
                    </button>
                @endif
            </div>

            {{-- Aviso: promoción no aplicable por pedido mínimo --}}
            @if($promotionWarning)
                <div style="background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.3); color: #f87171; padding: 0.6rem 0.8rem; border-radius: 10px; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.75rem;">
                    {{ $promotionWarning }}
                </div>
            @endif

            {{-- Subtotal --}}
            @if($discountAmount > 0)
                <div class="promo-discount-row">
                    <span class="ticket-total-label">Subtotal</span>
                    <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted);">Bs. {{ number_format($this->subtotal, 2) }}</span>
                </div>
                <div class="promo-discount-row">
                    <span class="promo-discount-label">Descuento</span>
                    <span class="promo-discount-value">-Bs. {{ number_format($discountAmount, 2) }}</span>
                </div>
            @endif

            <div class="ticket-total-row">
                <span class="ticket-total-label">Total a Pagar</span>
                <span class="ticket-total-value">Bs. {{ number_format($this->total, 2) }}</span>
            </div>

            {{-- Sección de Calculadora de Cambio / Billete --}}
            <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 14px; padding: 0.75rem 0.85rem; margin-bottom: 0.85rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); display: flex; align-items: center; gap: 0.3rem;">
                        💵 Monto del Billete (Efectivo)
                    </label>
                    @if($cashReceived !== '' && (float)$cashReceived > 0)
                        <button type="button" wire:click="$set('cashReceived', '')" style="background: transparent; border: none; color: var(--text-muted); font-size: 0.68rem; font-weight: 700; cursor: pointer; text-decoration: underline;">
                            Limpiar
                        </button>
                    @endif
                </div>

                {{-- Input y Botón Exacto --}}
                <div style="display: flex; gap: 0.4rem; margin-bottom: 0.45rem;">
                    <div style="position: relative; flex: 1;">
                        <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); font-weight: 800; font-size: 0.8rem; color: var(--text-muted);">Bs.</span>
                        <input type="number" step="0.5" min="0" inputmode="decimal"
                               wire:model.live.debounce.150ms="cashReceived"
                               placeholder="0.00"
                               style="width: 100%; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-base); color: var(--text-strong); font-size: 0.95rem; font-weight: 800; padding: 0.45rem 0.6rem 0.45rem 2.2rem; outline: none; font-family: inherit;">
                    </div>
                    <button type="button" wire:click="setBillAmount('exact')"
                            style="padding: 0.45rem 0.65rem; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #60a5fa; border-radius: 10px; font-weight: 700; font-size: 0.75rem; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                        Exacto
                    </button>
                </div>

                {{-- Billetes Rápidos (Bolivia) --}}
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.3rem; margin-bottom: 0.45rem;">
                    @foreach([20, 50, 100, 200] as $bill)
                        <button type="button" wire:click="setBillAmount({{ $bill }})"
                                style="padding: 0.3rem 0.2rem; background: {{ (float)$cashReceived === (float)$bill ? 'linear-gradient(135deg, #dc2626, #b91c1c)' : 'var(--bg-base)' }}; color: {{ (float)$cashReceived === (float)$bill ? '#fff' : 'var(--text-strong)' }}; border: 1px solid {{ (float)$cashReceived === (float)$bill ? '#dc2626' : 'var(--border)' }}; border-radius: 8px; font-weight: 700; font-size: 0.72rem; cursor: pointer; transition: all 0.15s; text-align: center;">
                            Bs. {{ $bill }}
                        </button>
                    @endforeach
                </div>

                {{-- Display del Cambio --}}
                @if($cashReceived !== '' && (float)$cashReceived > 0)
                    @if((float)$cashReceived >= (float)$this->total)
                        <div style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.35); border-radius: 10px; padding: 0.5rem 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; font-weight: 800; color: #22c55e; text-transform: uppercase; letter-spacing: 0.04em;">
                                Cambio a entregar
                            </span>
                            <span style="font-size: 1.2rem; font-weight: 900; color: #22c55e;">
                                Bs. {{ number_format($this->cashChange, 2) }}
                            </span>
                        </div>
                    @else
                        <div style="background: rgba(249, 115, 22, 0.12); border: 1px solid rgba(249, 115, 22, 0.35); border-radius: 10px; padding: 0.5rem 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.72rem; font-weight: 800; color: #f97316; text-transform: uppercase; letter-spacing: 0.04em;">
                                Falta para completar
                            </span>
                            <span style="font-size: 1.05rem; font-weight: 900; color: #f97316;">
                                Bs. {{ number_format($this->cashMissing, 2) }}
                            </span>
                        </div>
                    @endif
                @endif
            </div>
            
            @if($editingOrderId)
                <div style="display: flex; gap: 0.5rem;">
                    <button wire:click="cancelEditing" style="flex: 1; background: var(--bg-elevated); color: var(--text-muted); border: 1px solid var(--border); padding: 1rem; border-radius: 12px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        CANCELAR
                    </button>
                    <button wire:click="updateOrder" class="btn-send-kitchen" style="flex: 2; background: linear-gradient(135deg, #10b981, #059669);">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        GUARDAR CAMBIOS
                    </button>
                </div>
            @else
                <div style="display: flex; gap: 0.5rem;">
                    @if(!$tableId)
                        <button wire:click="loadUnpaidOrders" style="flex: 1; background: #3b82f6; color: white; border: none; padding: 1rem; border-radius: 12px; font-weight: 800; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            PENDIENTES
                        </button>
                    @endif
                    <button wire:click="submitOrder" class="btn-send-kitchen" style="{{ !$tableId ? 'flex: 2;' : 'width: 100%;' }}">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        ENVIAR A COCINA
                    </button>
                </div>
            @endif
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

        document.addEventListener('livewire:init', () => {
            const showAlert = (e) => {
                const msg = Array.isArray(e) ? (e[0]?.message) : e?.message;
                if (msg) window.alert(msg);
            };
            Livewire.on('stock-alert', showAlert);
            Livewire.on('pos-error', showAlert);
        });
    </script>

    <!-- Modal de Salsas Drawer/Overlay -->
    @if($showSauceModal)
    <div class="sauce-modal-overlay">
        <div class="sauce-modal">
            <div class="sauce-modal-header">
                <div>
                    <h3>{{ $sauceStep === 1 ? 'Paso 1: Elige tus Salsas' : ($tempIsWingsProduct ? 'Paso 2: Asignar alitas a bañar' : 'Paso 2: Presentación de Salsas') }}</h3>
                    @if($sauceStep === 1)
                        <p>Puedes elegir hasta {{ $tempProductMaxSauces }} {{ $tempProductMaxSauces == 1 ? 'salsa' : 'salsas' }} distintas.</p>
                    @elseif($tempIsWingsProduct)
                        <p>Total de alitas disponibles: {{ $tempProductWingsCount }}</p>
                    @else
                        <p>Indica si tus salsas irán bañadas o aparte.</p>
                    @endif
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    {{-- Control de cantidad dentro del modal --}}
                    <div style="display: flex; align-items: center; gap: 0.4rem; background: var(--bg-surface); padding: 0.35rem 0.75rem; border-radius: 12px; border: 1px solid var(--border-strong);">
                        <span style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted);">Cant:</span>
                        <button type="button" wire:click="decrementTempQuantity" style="width:32px; height:32px; font-weight:900; font-size:1rem; border-radius:8px; background:var(--bg-base); border:1px solid var(--border-strong); color:var(--text-strong); cursor:pointer; display:flex; align-items:center; justify-content:center;">-</button>
                        <span style="font-weight: 900; font-size: 1.2rem; color: #dc2626; min-width: 24px; text-align: center;">{{ $tempItemQuantity }}</span>
                        <button type="button" wire:click="incrementTempQuantity" style="width:32px; height:32px; font-weight:900; font-size:1rem; border-radius:8px; background:var(--bg-base); border:1px solid var(--border-strong); color:var(--text-strong); cursor:pointer; display:flex; align-items:center; justify-content:center;">+</button>
                    </div>
                    <button wire:click="$set('showSauceModal', false)" class="sauce-modal-close">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            
            <div class="sauce-modal-body">
                @if($sauceStep === 1)
                    <!-- Paso 1: Elegir Salsas -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.85rem;">
                        @foreach($allSauces as $sauce)
                            @php
                                $isSelected = in_array($sauce->id, $tempSelectedSauceIds);
                                $isDisabled = !$isSelected && count($tempSelectedSauceIds) >= $tempProductMaxSauces;
                                $sauceDisplayName = is_array($sauce->name ?? null) ? implode(', ', $sauce->name) : ($sauce->name ?? 'Salsa');
                            @endphp
                            <button wire:click="toggleSauceSelection({{ $sauce->id }})" 
                                     class="sauce-row {{ $isSelected ? 'sauce-row-active' : '' }}"
                                     style="margin-bottom:0; width: 100%; min-height: 85px; padding: 1.1rem; text-align: left; cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }}; opacity: {{ $isDisabled ? '0.4' : '1' }}; border: 2px solid {{ $isSelected ? '#dc2626' : 'var(--border)' }}; background: {{ $isSelected ? 'rgba(220, 38, 38, 0.08)' : 'var(--bg-base)' }}; border-radius: 16px; transition: all 0.15s ease;">
                                <div style="width: 100%;">
                                    <h4 class="sauce-name" style="display:flex; justify-content:space-between; align-items:center; font-size: 1.15rem; font-weight: 800;">
                                        <span>{{ $sauceDisplayName }}</span>
                                        @if($isSelected)
                                            <span style="background: #dc2626; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </span>
                                        @endif
                                    </h4>
                                    <div class="sauce-spice" style="margin-top: 0.4rem; display: flex; gap: 3px; align-items: center;">
                                        @for($i = 0; $i < $sauce->spice_level; $i++)
                                            <svg width="16" height="16" fill="#dc2626" viewBox="0 0 24 24"><path d="M12 2C9 6 7 9 7 13a5 5 0 0010 0c0-1.5-.5-3-1.5-4.5C15 11 13.5 12 12 12c1-2 1-5 0-10z"></path></svg>
                                        @endfor
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    @if(count($tempSelectedSauceIds) > 0)
                        <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px dashed var(--border);">
                            <button type="button" wire:click="quickConfirmCurrentSauces(true)"
                                    style="flex: 1; padding: 1.1rem; background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; font-weight: 900; font-size: 1.05rem; border-radius: 14px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);">
                                ⚡ Confirmar (Todas Bañadas)
                            </button>
                            <button type="button" wire:click="quickConfirmCurrentSauces(false)"
                                    style="flex: 1; padding: 1.1rem; background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; font-weight: 900; font-size: 1.05rem; border-radius: 14px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(249, 115, 22, 0.3);">
                                ⚡ Confirmar (Todas Aparte)
                            </button>
                        </div>
                    @endif
                @else
                    <!-- Paso 2: Configuración por Porción / Unidad -->
                    @php 
                        $totalUnits = max(1, (int) $tempItemQuantity);
                        $wingsPerUnit = $tempItemQuantity > 0 ? (int) ($tempProductWingsCount / $tempItemQuantity) : $tempProductWingsCount; 
                        $singleSauce = count($tempSelectedSauceIds) === 1;
                    @endphp

                    @if($singleSauce)
                        {{-- ═══ UNA SOLA SALSA: Vista simplificada ═══ --}}
                        @php 
                            $onlySauceId = $tempSelectedSauceIds[0] ?? null;
                            $onlySauce = $onlySauceId ? $allSauces->firstWhere('id', $onlySauceId) : null;
                            $onlySauceName = $onlySauce ? (is_array($onlySauce->name ?? null) ? implode(', ', $onlySauce->name) : ($onlySauce->name ?? 'Salsa')) : 'Salsa';
                        @endphp
                        @if($onlySauce)
                            <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; padding: 1rem; margin-bottom: 0.75rem;">
                                <div style="font-weight: 800; font-size: 1rem; color: #dc2626; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                                    <span>🧂 {{ $onlySauceName }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                        — {{ $totalUnits }} {{ $totalUnits == 1 ? 'porción' : 'porciones' }}
                                    </span>
                                </div>

                                @if($tempIsWingsProduct)
                                    {{-- Producto de alitas: controles bañadas/aparte con cantidades --}}
                                    @php
                                        $wValSingleRaw = isset($tempSauceWingCounts[0][$onlySauce->id]) ? $tempSauceWingCounts[0][$onlySauce->id] : ($tempSauceWingCounts[$onlySauce->id] ?? 0);
                                        $sValSingleRaw = isset($tempSauceSideCounts[0][$onlySauce->id]) ? $tempSauceSideCounts[0][$onlySauce->id] : ($tempSauceSideCounts[$onlySauce->id] ?? 0);
                                        $wValSingle = is_array($wValSingleRaw) ? 0 : intval($wValSingleRaw);
                                        $sValSingle = is_array($sValSingleRaw) ? 0 : intval($sValSingleRaw);
                                    @endphp
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); padding: 0.5rem 0.7rem; border-radius: 8px;">
                                            <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 700;">🌊 Bañadas</span>
                                            <div class="sauce-counter" style="display: flex; align-items: center; gap: 0.3rem;">
                                                <button type="button" wire:click="decrementSauceWings({{ $onlySauce->id }}, 0)" class="sauce-counter-btn" style="{{ $wValSingle <= 0 ? 'opacity:0.3;cursor:not-allowed;' : '' }}">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                </button>
                                                <input type="number" min="0" max="{{ $tempProductWingsCount }}"
                                                       wire:key="input-wings-s1-{{ $onlySauce->id }}-{{ $wValSingle }}-{{ $sValSingle }}"
                                                       value="{{ $wValSingle }}"
                                                       onfocus="this.select()"
                                                       wire:change="updateSauceWings({{ $onlySauce->id }}, 0, $event.target.value)"
                                                       style="width: 55px; text-align: center; font-weight: 900; font-size: 1.05rem; color: #dc2626; background: var(--bg-surface); border: 1px solid var(--border-strong); border-radius: 8px; padding: 0.25rem 0; outline: none; -moz-appearance: textfield;">
                                                <button type="button" wire:click="incrementSauceWings({{ $onlySauce->id }}, 0)" class="sauce-counter-btn">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); padding: 0.5rem 0.7rem; border-radius: 8px;">
                                            <span style="font-size: 0.85rem; color: #f97316; font-weight: 700;">🥣 Aparte</span>
                                            <div class="sauce-counter" style="display: flex; align-items: center; gap: 0.3rem;">
                                                <button type="button" wire:click="decrementSauceSide({{ $onlySauce->id }}, 0)" class="sauce-counter-btn" style="{{ $sValSingle <= 0 ? 'opacity:0.3;cursor:not-allowed;' : '' }}">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                </button>
                                                <input type="number" min="0" max="{{ $tempProductWingsCount }}"
                                                       wire:key="input-side-s1-{{ $onlySauce->id }}-{{ $wValSingle }}-{{ $sValSingle }}"
                                                       value="{{ $sValSingle }}"
                                                       onfocus="this.select()"
                                                       wire:change="updateSauceSide({{ $onlySauce->id }}, 0, $event.target.value)"
                                                       style="width: 55px; text-align: center; font-weight: 900; font-size: 1.05rem; color: #f97316; background: var(--bg-surface); border: 1px solid var(--border-strong); border-radius: 8px; padding: 0.25rem 0; outline: none; -moz-appearance: textfield;">
                                                <button type="button" wire:click="incrementSauceSide({{ $onlySauce->id }}, 0)" class="sauce-counter-btn">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div style="text-align: center; font-size: 0.78rem; color: var(--text-muted); padding-top: 0.3rem;">
                                            {{ $wValSingle + $sValSingle }}/{{ $tempProductWingsCount }} alitas asignadas
                                        </div>
                                    </div>
                                @else
                                    {{-- Producto sin alitas: solo bañada/aparte --}}
                                    @php
                                        $wValNonW = isset($tempSauceWingCounts[0][$onlySauce->id]) ? $tempSauceWingCounts[0][$onlySauce->id] : ($tempSauceWingCounts[$onlySauce->id] ?? 0);
                                        $isCoatedSingle = is_array($wValNonW) ? false : intval($wValNonW) > 0;
                                    @endphp
                                    <div style="display: flex; gap: 0.6rem;">
                                        <button type="button" wire:click="setSauceCoated({{ $onlySauce->id }}, true, 0)"
                                                style="flex: 1; padding: 0.7rem; border-radius: 10px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.2s ease; border: 2px solid {{ $isCoatedSingle ? '#dc2626' : 'var(--border)' }}; background: {{ $isCoatedSingle ? '#fee2e2' : 'var(--bg-base)' }}; color: {{ $isCoatedSingle ? '#991b1b' : 'var(--text-strong)' }};">
                                            🌊 Bañada
                                        </button>
                                        <button type="button" wire:click="setSauceCoated({{ $onlySauce->id }}, false, 0)"
                                                style="flex: 1; padding: 0.7rem; border-radius: 10px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.2s ease; border: 2px solid {{ !$isCoatedSingle ? '#f97316' : 'var(--border)' }}; background: {{ !$isCoatedSingle ? '#ffedd5' : 'var(--bg-base)' }}; color: {{ !$isCoatedSingle ? '#c2410c' : 'var(--text-strong)' }};">
                                            🥣 Aparte
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif

                    @else
                        {{-- ═══ MÚLTIPLES SALSAS: Vista por porción ═══ --}}

                        {{-- Barra de accesos rápidos --}}
                        <div style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 14px; padding: 0.85rem; margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;">
                            <span style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: #60a5fa; letter-spacing: 0.04em;">
                                ⚡ Accesos Rápidos de Salsas {{ $totalUnits > 1 ? '('.$totalUnits.' porciones)' : '' }}
                            </span>
                            <div style="display: flex; gap: 0.6rem;">
                                @if($totalUnits > 1)
                                    <button type="button" wire:click="copyUnitOneToAll"
                                            style="flex: 1; padding: 0.65rem; background: var(--bg-surface); border: 1px solid rgba(59, 130, 246, 0.3); color: var(--text-strong); font-weight: 800; font-size: 0.85rem; border-radius: 10px; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                                        📋 Copiar Porción #1 a Todas
                                    </button>
                                @endif
                                <button type="button" wire:click="setAllUnitsCoated(true)"
                                        style="flex: 1; padding: 0.65rem; background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.3); color: #f87171; font-weight: 800; font-size: 0.85rem; border-radius: 10px; cursor: pointer; transition: all 0.2s;">
                                    🌊 Todas Bañadas
                                </button>
                                <button type="button" wire:click="setAllUnitsCoated(false)"
                                        style="flex: 1; padding: 0.65rem; background: rgba(249, 115, 22, 0.1); border: 1px solid rgba(249, 115, 22, 0.3); color: #fb923c; font-weight: 800; font-size: 0.85rem; border-radius: 10px; cursor: pointer; transition: all 0.2s;">
                                    🥣 Todas Aparte
                                </button>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1rem;">
                            @for($u = 0; $u < $totalUnits; $u++)
                            <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; padding: 0.85rem; margin-bottom: 0.75rem;">
                                <div style="font-weight: 800; font-size: 0.95rem; color: #dc2626; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.35rem;">
                                    <span>📦 Porción #{{ $u + 1 }} {{ $totalUnits > 1 ? '(Producto ' . chr(65 + $u) . ')' : '' }}</span>
                                    @if($tempIsWingsProduct)
                                        @php 
                                            $unitSum = $this->sumSauceCounts($tempSauceWingCounts, $u) + $this->sumSauceCounts($tempSauceSideCounts, $u);
                                        @endphp
                                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                            {{ $unitSum }}/{{ $wingsPerUnit }} alitas asignadas
                                        </span>
                                    @endif
                                </div>

                                @foreach($tempSelectedSauceIds as $sauceId)
                                    @php $s = $allSauces->firstWhere('id', $sauceId); @endphp
                                    @if($s)
                                        @php
                                            $wValRaw = isset($tempSauceWingCounts[$u][$s->id]) ? $tempSauceWingCounts[$u][$s->id] : ($tempSauceWingCounts[$s->id] ?? 0);
                                            $sValRaw = isset($tempSauceSideCounts[$u][$s->id]) ? $tempSauceSideCounts[$u][$s->id] : ($tempSauceSideCounts[$s->id] ?? 0);
                                            $wVal = is_array($wValRaw) ? 0 : intval($wValRaw);
                                            $sVal = is_array($sValRaw) ? 0 : intval($sValRaw);
                                            $sDisplayName = is_array($s->name ?? null) ? implode(', ', $s->name) : ($s->name ?? 'Salsa');
                                        @endphp
                                        @if($tempIsWingsProduct)
                                            <div wire:key="sauce-wing-{{ $u }}-{{ $s->id }}" class="sauce-row" style="flex-direction: column; align-items: stretch; gap: 0.4rem; padding: 0.6rem; margin-bottom: 0.5rem;">
                                                <h4 class="sauce-name" style="margin-bottom: 0.2rem; font-size: 0.85rem;">{{ $sDisplayName }}</h4>
                                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); padding: 0.35rem 0.5rem; border-radius: 6px;">
                                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">Bañadas</span>
                                                    <div class="sauce-counter" style="display: flex; align-items: center; gap: 0.25rem;">
                                                        <button type="button" wire:click="decrementSauceWings({{ $s->id }}, {{ $u }})" class="sauce-counter-btn" style="{{ $wVal <= 0 ? 'opacity:0.3;cursor:not-allowed;' : '' }}">
                                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                        </button>
                                                        <input type="number" min="0" max="{{ $wingsPerUnit }}"
                                                               wire:key="input-wings-m-{{ $u }}-{{ $s->id }}-{{ $wVal }}-{{ $sVal }}"
                                                               value="{{ $wVal }}"
                                                               onfocus="this.select()"
                                                               wire:change="updateSauceWings({{ $s->id }}, {{ $u }}, $event.target.value)"
                                                               style="width: 50px; text-align: center; font-weight: 900; font-size: 0.95rem; color: #dc2626; background: var(--bg-surface); border: 1px solid var(--border-strong); border-radius: 8px; padding: 0.2rem 0; outline: none; -moz-appearance: textfield;">
                                                        <button type="button" wire:click="incrementSauceWings({{ $s->id }}, {{ $u }})" class="sauce-counter-btn">
                                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); padding: 0.35rem 0.5rem; border-radius: 6px;">
                                                    <span style="font-size: 0.75rem; color: #f97316; font-weight: 700;">Aparte</span>
                                                    <div class="sauce-counter" style="display: flex; align-items: center; gap: 0.25rem;">
                                                        <button type="button" wire:click="decrementSauceSide({{ $s->id }}, {{ $u }})" class="sauce-counter-btn" style="{{ $sVal <= 0 ? 'opacity:0.3;cursor:not-allowed;' : '' }}">
                                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                        </button>
                                                        <input type="number" min="0" max="{{ $wingsPerUnit }}"
                                                               wire:key="input-side-m-{{ $u }}-{{ $s->id }}-{{ $wVal }}-{{ $sVal }}"
                                                               value="{{ $sVal }}"
                                                               onfocus="this.select()"
                                                               wire:change="updateSauceSide({{ $s->id }}, {{ $u }}, $event.target.value)"
                                                               style="width: 50px; text-align: center; font-weight: 900; font-size: 0.95rem; color: #f97316; background: var(--bg-surface); border: 1px solid var(--border-strong); border-radius: 8px; padding: 0.2rem 0; outline: none; -moz-appearance: textfield;">
                                                        <button type="button" wire:click="incrementSauceSide({{ $s->id }}, {{ $u }})" class="sauce-counter-btn">
                                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <!-- No-alitas (Picadas, etc) -->
                                            @php $isCoated = $wVal > 0; @endphp
                                            <div wire:key="sauce-nonwing-{{ $u }}-{{ $s->id }}" class="sauce-row" style="flex-direction: column; align-items: stretch; gap: 0.4rem; padding: 0.6rem; margin-bottom: 0.5rem;">
                                                <h4 class="sauce-name" style="margin-bottom: 0.2rem; font-size: 0.85rem;">{{ $sDisplayName }}</h4>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <button type="button" wire:click="setSauceCoated({{ $s->id }}, true, {{ $u }})"
                                                            style="flex: 1; padding: 0.4rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease; border: 2px solid {{ $isCoated ? '#dc2626' : 'var(--border)' }}; background: {{ $isCoated ? '#fee2e2' : 'var(--bg-base)' }}; color: {{ $isCoated ? '#991b1b' : 'var(--text-strong)' }};">
                                                        🌊 Bañada
                                                    </button>
                                                    <button type="button" wire:click="setSauceCoated({{ $s->id }}, false, {{ $u }})"
                                                            style="flex: 1; padding: 0.4rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease; border: 2px solid {{ !$isCoated ? '#f97316' : 'var(--border)' }}; background: {{ !$isCoated ? '#ffedd5' : 'var(--bg-base)' }}; color: {{ !$isCoated ? '#c2410c' : 'var(--text-strong)' }};">
                                                        🥣 Aparte
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        @endfor
                        </div>
                    @endif
                @endif
            </div>

            <div class="sauce-modal-footer">
                @if($sauceStep === 1)
                    <button wire:click="goToSauceStep2" class="btn-confirm-sauces btn-confirm-sauces-ready">
                        CONTINUAR
                    </button>
                @else
                    <div style="display: flex; gap: 0.5rem;">
                        <button wire:click="goToSauceStep1" class="btn-confirm-sauces" style="background: transparent; border: 1px solid var(--border-strong); color: var(--text-strong); width: 30%;">
                            Volver
                        </button>
                        <button wire:click="confirmSauces" class="btn-confirm-sauces btn-confirm-sauces-ready" style="width: 70%;">
                            CONFIRMAR {{ $tempIsWingsProduct ? '(' . $this->tempSauceWingsTotal . '/' . $tempProductWingsCount . ' alitas)' : '' }}
                        </button>
                    </div>
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
                <h3>Seleccionar Promoción</h3>
                <button wire:click="$set('showPromoModal', false)" style="background:transparent;border:1px solid var(--border);color:var(--text-muted);width:36px;height:36px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
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
                                        @case('discount') Descuento @break
                                        @case('combo') Combo @break
                                        @case('birthday') Cumpleaños @break
                                        @case('free_item') Gratis @break
                                        @case('custom') Especial @break
                                    @endswitch
                                </span>
                                <span class="promo-option-tag promo-option-tag-value">
                                    @if(data_get($promo, 'discount_type') === 'percentage')
                                        {{ data_get($promo, 'discount_value') }}% OFF
                                    @elseif(data_get($promo, 'discount_type') === 'fixed')
                                        -Bs. {{ number_format(data_get($promo, 'discount_value'), 2) }}
                                    @else
                                        Gratis x{{ data_get($promo, 'free_quantity') }}
                                    @endif
                                </span>
                                <span class="promo-option-tag promo-option-tag-branch">
                                    {{ data_get($promo, 'branch.name', 'Todas') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="promo-empty-list">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <p>No hay promociones activas disponibles.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ MODAL DE PAGO: pedido de cocina (para llevar / delivery) ═══ --}}
    @if($showPaymentModal)
    <div style="position: fixed; inset: 0; z-index: 90; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.7); backdrop-filter: blur(6px);">
        <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 20px; width: 100%; max-width: 420px; overflow: hidden;">
            <div style="padding: 1.25rem 1.5rem; background: var(--bg-base); border-bottom: 1px solid var(--border);">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-strong);">Cobrar pedido de cocina</h3>
                <p style="color: var(--text-muted); font-size: 0.82rem; margin-top: 0.25rem;">
                    {{ $orderType === 'delivery' ? 'Delivery' : 'Para llevar' }} — se cobra al enviar a cocina.
                </p>
            </div>
            <div style="padding: 1.5rem;">
                <div style="text-align: center; margin-bottom: 1.25rem;">
                    <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">Total a pagar</div>
                    <div style="font-size: 2rem; font-weight: 900; color: #f97316;">Bs. {{ number_format($this->pendingOrderId ? $this->pendingOrderTotal : $this->total, 2) }}</div>
                </div>
                @include('partials.payment-lines')

                <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem;">
                    <button wire:click="$set('showPaymentModal', false)" style="flex: 1; background: var(--bg-elevated); color: var(--text-muted); border: 1px solid var(--border); padding: 0.85rem; border-radius: 12px; font-weight: 700; font-size: 0.9rem; cursor: pointer;">
                        Cancelar
                    </button>
                    <button wire:click="confirmTakeawayUnpaid" style="flex: 1.5; background: #3b82f6; color: #fff; border: none; padding: 0.85rem; border-radius: 12px; font-weight: 800; font-size: 0.9rem; cursor: pointer;">
                        <span wire:loading.remove wire:target="confirmTakeawayUnpaid">Por cobrar</span>
                        <span wire:loading wire:target="confirmTakeawayUnpaid">Registrando…</span>
                    </button>
                    <button wire:click="confirmTakeawayPayment"
                            @disabled(!$this->pagoCubierto)
                            style="flex: 1.5; background: {{ $this->pagoCubierto ? 'linear-gradient(135deg, #f97316, #dc2626)' : 'var(--border-strong)' }}; color: #fff; border: none; padding: 0.85rem; border-radius: 12px; font-weight: 800; font-size: 0.9rem; cursor: {{ $this->pagoCubierto ? 'pointer' : 'not-allowed' }}; opacity: {{ $this->pagoCubierto ? '1' : '0.6' }};">
                        <span wire:loading.remove wire:target="confirmTakeawayPayment">Cobrar ahora</span>
                        <span wire:loading wire:target="confirmTakeawayPayment">Registrando…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    {{-- ═══ MODAL DE PEDIDOS PENDIENTES ═══ --}}


    @if($showUnpaidOrdersModal)
    <div style="position: fixed; inset: 0; z-index: 90; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.7); backdrop-filter: blur(6px);">
        <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 20px; width: 100%; max-width: 500px; overflow: hidden; display: flex; flex-direction: column; max-height: 80vh;">
            <div style="padding: 1.25rem 1.5rem; background: var(--bg-base); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-strong);">Pedidos por Cobrar</h3>
                <button wire:click="$set('showUnpaidOrdersModal', false)" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div style="padding: 1.5rem; overflow-y: auto;">
                @if(count($unpaidOrders) === 0)
                    <div style="text-align: center; color: var(--text-muted); padding: 2rem 0;">
                        No hay pedidos de delivery o local pendientes de cobro.
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($unpaidOrders as $uo)
                        <div style="background: var(--bg-base); border: 1px solid var(--border); border-radius: 12px; padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 800; color: var(--text-strong);">{{ $uo->daily_label }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">
                                    {{ $uo->order_type === 'delivery' ? '🛵 Delivery' : '🥡 Recoger' }} • Bs. {{ number_format($uo->total, 2) }}
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button wire:click="loadOrderForEditing({{ $uo->id }})" style="background: rgba(249, 115, 22, 0.15); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.4); padding: 0.5rem 0.8rem; border-radius: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                                    Editar
                                </button>
                                <button wire:click="confirmCancelPendingOrder({{ $uo->id }})" style="background: transparent; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); padding: 0.5rem 0.8rem; border-radius: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                                    Cancelar
                                </button>
                                <button wire:click="payUnpaidOrder({{ $uo->id }})" style="background: #10b981; color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                                    Cobrar
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Cancelar Pedido Pendiente -->
    @if($showCancelOrderModal)
    <div style="position: fixed; inset: 0; z-index: 100; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px);">
        <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 20px; width: 100%; max-width: 420px; overflow: hidden; display: flex; flex-direction: column;">
            <div style="padding: 1.25rem 1.5rem; background: var(--bg-base); display: flex; align-items: center; gap: 0.5rem;">
                <h3 style="color: #dc2626; font-size: 1.15rem; font-weight: 800; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Cancelar Pedido
                </h3>
            </div>
            <div style="padding: 1.5rem; padding-top: 0.5rem;">
                <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.5;">
                    ¿Estás seguro que deseas cancelar este pedido?<br><br>
                    Esta acción es irreversible y se eliminará de la lista de pendientes de cobro.
                </p>
                
                <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem;">
                    <button wire:click="$set('showCancelOrderModal', false)" style="flex: 1; background: var(--bg-elevated); color: var(--text-muted); border: 1px solid var(--border); padding: 0.75rem; border-radius: 12px; font-weight: 700; cursor: pointer;">
                        Volver
                    </button>
                    <button wire:click="cancelPendingOrder" style="flex: 1.5; background: #dc2626; color: #fff; border: none; padding: 0.75rem; border-radius: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);">
                        Sí, Cancelar Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ MODAL SELECCIONAR MESA (Comer aquí sin mesa asignada) ═══ --}}
    @if($showTableSelectModal)
    <div style="position: fixed; inset: 0; z-index: 100; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px);">
        <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 20px; width: 100%; max-width: 480px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
            <div style="padding: 1.25rem 1.5rem; background: var(--bg-base); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-strong); display: flex; align-items: center; gap: 0.4rem;">
                        🛋️ Selecciona la Mesa (Salón)
                    </h3>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">¿En qué mesa se servirá la orden?</p>
                </div>
                <button wire:click="$set('showTableSelectModal', false)" style="background: transparent; border: none; color: var(--text-muted); font-size: 1.1rem; cursor: pointer;">
                    ✕
                </button>
            </div>
            <div style="padding: 1.25rem; max-height: 60vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.6rem;">
                    @foreach($this->availableTables as $tbl)
                        <div style="display: flex; flex-direction: column; background: {{ $tbl->status === 'occupied' ? 'rgba(249, 115, 22, 0.08)' : 'var(--bg-base)' }}; border: 2px solid {{ $tableId == $tbl->id ? '#dc2626' : ($tbl->status === 'occupied' ? '#f97316' : 'var(--border)') }}; border-radius: 14px; overflow: hidden; transition: all 0.2s ease;">
                            <button type="button" wire:click="confirmTableSelectAndSubmit({{ $tbl->id }})"
                                    style="padding: 0.85rem 0.5rem 0.4rem; background: transparent; border: none; cursor: pointer; text-align: center; width: 100%;">
                                <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-strong);">{{ $tbl->name }}</div>
                                <div style="font-size: 0.68rem; font-weight: 700; margin-top: 0.2rem; color: {{ $tbl->status === 'occupied' ? '#f97316' : '#22c55e' }};">
                                    {{ $tbl->status === 'occupied' ? 'Ocupada' : 'Disponible' }}
                                </div>
                            </button>
                            <div style="display: flex; border-top: 1px dashed var(--border); padding: 0.2rem;">
                                @if($tbl->status === 'occupied')
                                    <button type="button" wire:click.stop="liberateTable({{ $tbl->id }})"
                                            style="flex: 1; padding: 0.25rem; font-size: 0.65rem; font-weight: 700; background: rgba(34, 197, 94, 0.15); color: #22c55e; border: none; border-radius: 6px; cursor: pointer;">
                                        🟢 Liberar
                                    </button>
                                @else
                                    <button type="button" wire:click.stop="occupyTable({{ $tbl->id }})"
                                            style="flex: 1; padding: 0.25rem; font-size: 0.65rem; font-weight: 700; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: none; border-radius: 6px; cursor: pointer;">
                                        🔴 Ocupar
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div style="padding: 1rem 1.25rem; background: var(--bg-base); border-top: 1px solid var(--border);">
                <button wire:click="$set('showTableSelectModal', false)" style="width: 100%; padding: 0.75rem; background: var(--bg-elevated); border: 1px solid var(--border); color: var(--text-muted); font-weight: 700; border-radius: 12px; cursor: pointer;">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

</div>