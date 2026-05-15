<div>
    <style>
        .promo-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .promo-title { font-size: 1.75rem; font-weight: 900; background: linear-gradient(135deg, #f97316, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .btn-new-promo {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.5rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; font-weight: 800;
            font-size: 0.85rem; border: none; border-radius: 14px; cursor: pointer; transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .btn-new-promo:hover { background: linear-gradient(135deg, #ef4444, #dc2626); transform: translateY(-2px); }

        .promo-filters { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .promo-select {
            background: #111; border: 1px solid #222; color: #ccc; padding: 0.6rem 1rem;
            border-radius: 12px; font-size: 0.85rem; font-family: inherit; outline: none;
        }
        .promo-select:focus { border-color: #f97316; }

        /* Cards grid */
        .promo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1rem; }

        .promo-card {
            background: #111; border: 1px solid #222; border-radius: 18px; padding: 1.25rem;
            transition: all 0.2s; position: relative; overflow: hidden;
        }
        .promo-card:hover { border-color: #333; transform: translateY(-2px); }
        .promo-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            border-radius: 18px 18px 0 0;
        }
        .promo-card-active::before { background: linear-gradient(90deg, #22c55e, #16a34a); }
        .promo-card-inactive::before { background: #333; }

        .promo-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; }
        .promo-card-name { font-weight: 800; color: #e5e5e5; font-size: 1rem; }
        .promo-card-desc { font-size: 0.8rem; color: #666; margin-bottom: 0.75rem; line-height: 1.4; }

        .promo-tags { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.75rem; }
        .promo-tag {
            font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 50px;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .promo-tag-type { background: rgba(139, 92, 246, 0.1); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.2); }
        .promo-tag-discount { background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.2); }
        .promo-tag-branch { background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2); }
        .promo-tag-dates { background: rgba(156, 163, 175, 0.1); color: #9ca3af; border: 1px solid rgba(156, 163, 175, 0.15); }

        .promo-card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid #1e1e1e; }

        .promo-toggle {
            position: relative; width: 44px; height: 24px; background: #333; border-radius: 50px;
            cursor: pointer; transition: background 0.3s; border: none;
        }
        .promo-toggle-on { background: #22c55e; }
        .promo-toggle::after {
            content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px;
            background: #fff; border-radius: 50%; transition: transform 0.3s;
        }
        .promo-toggle-on::after { transform: translateX(20px); }

        .btn-edit-promo {
            background: transparent; border: 1px solid #2a2a2a; color: #f97316; padding: 0.35rem 0.75rem;
            border-radius: 10px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s;
        }
        .btn-edit-promo:hover { background: #1a1a1a; border-color: #f97316; }

        .promo-empty { text-align: center; padding: 4rem 2rem; color: #444; }
        .promo-empty span { font-size: 3rem; display: block; margin-bottom: 0.75rem; opacity: 0.3; }

        .promo-success {
            background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2);
            color: #22c55e; padding: 0.75rem 1rem; border-radius: 12px; font-size: 0.85rem;
            font-weight: 600; margin-bottom: 1rem;
        }

        /* Modal */
        .promo-modal-overlay {
            position: fixed; inset: 0; z-index: 50; display: flex; align-items: center;
            justify-content: center; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px);
        }
        .promo-modal {
            background: #111; border: 1px solid #222; width: 100%; max-width: 580px;
            border-radius: 20px; overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;
        }
        .promo-modal-header {
            padding: 1.25rem 1.5rem; background: #0a0a0a; border-bottom: 1px solid #222;
            display: flex; justify-content: space-between; align-items: center;
        }
        .promo-modal-header h3 { font-size: 1.15rem; font-weight: 800; color: #fff; }
        .promo-modal-body { padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1; }
        .promo-modal-footer { padding: 1.25rem 1.5rem; border-top: 1px solid #222; background: #0a0a0a; display: flex; gap: 0.75rem; justify-content: flex-end; }

        .promo-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .promo-form-group { margin-bottom: 1rem; }
        .promo-form-label { display: block; font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; }
        .promo-form-input, .promo-form-select, .promo-form-textarea {
            width: 100%; background: #0a0a0a; border: 1px solid #222; color: #fff;
            padding: 0.7rem 1rem; border-radius: 12px; font-size: 0.9rem; outline: none; font-family: inherit;
        }
        .promo-form-input:focus, .promo-form-select:focus, .promo-form-textarea:focus { border-color: #f97316; }
        .promo-form-textarea { resize: vertical; min-height: 60px; }

        .promo-form-check {
            display: flex; align-items: center; gap: 0.5rem; cursor: pointer;
        }
        .promo-form-check input[type=checkbox] {
            width: 18px; height: 18px; accent-color: #dc2626; cursor: pointer;
        }
        .promo-form-check label { font-size: 0.85rem; color: #ccc; font-weight: 600; cursor: pointer; }

        .btn-modal-cancel {
            padding: 0.7rem 1.5rem; background: #1a1a1a; color: #888; border: 1px solid #222;
            border-radius: 12px; font-weight: 700; font-size: 0.85rem; cursor: pointer;
        }
        .btn-modal-cancel:hover { color: #fff; border-color: #444; }
        .btn-modal-save {
            padding: 0.7rem 1.5rem; background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff;
            border: none; border-radius: 12px; font-weight: 800; font-size: 0.85rem; cursor: pointer;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .btn-modal-save:hover { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .promo-error { color: #f87171; font-size: 0.75rem; margin-top: 0.25rem; }
    </style>

    <div class="promo-header">
        <h1 class="promo-title">🎉 Gestión de Promociones</h1>
        <button wire:click="create" class="btn-new-promo">+ Nueva Promoción</button>
    </div>

    @if(session()->has('message'))
        <div class="promo-success">✅ {{ session('message') }}</div>
    @endif

    {{-- Filters --}}
    <div class="promo-filters">
        <select wire:model.live="filterBranch" class="promo-select">
            <option value="">Todas las sucursales</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterType" class="promo-select">
            <option value="">Todos los tipos</option>
            <option value="discount">Descuento</option>
            <option value="combo">Combo</option>
            <option value="birthday">Cumpleaños</option>
            <option value="free_item">Artículo Gratis</option>
            <option value="custom">Personalizada</option>
        </select>
    </div>

    {{-- Cards --}}
    @if($promotions->count() > 0)
        <div class="promo-grid">
            @foreach($promotions as $promo)
                <div class="promo-card {{ $promo->is_active ? 'promo-card-active' : 'promo-card-inactive' }}">
                    <div class="promo-card-header">
                        <h3 class="promo-card-name">{{ $promo->name }}</h3>
                    </div>

                    @if($promo->description)
                        <p class="promo-card-desc">{{ $promo->description }}</p>
                    @endif

                    <div class="promo-tags">
                        <span class="promo-tag promo-tag-type">
                            @switch($promo->type)
                                @case('discount') 💰 Descuento @break
                                @case('combo') 📦 Combo @break
                                @case('birthday') 🎂 Cumpleaños @break
                                @case('free_item') 🎁 Artículo Gratis @break
                                @case('custom') ⚙️ Personalizada @break
                            @endswitch
                        </span>

                        <span class="promo-tag promo-tag-discount">
                            @if($promo->discount_type === 'percentage')
                                {{ $promo->discount_value }}% OFF
                            @elseif($promo->discount_type === 'fixed')
                                -${{ number_format($promo->discount_value, 2) }}
                            @else
                                Gratis x{{ $promo->free_quantity }}
                            @endif
                        </span>

                        <span class="promo-tag promo-tag-branch">
                            {{ $promo->branch ? $promo->branch->name : '🌐 Todas' }}
                        </span>

                        @if($promo->starts_at || $promo->ends_at)
                            <span class="promo-tag promo-tag-dates">
                                📅 {{ $promo->starts_at ? $promo->starts_at->format('d/m') : '∞' }}
                                →
                                {{ $promo->ends_at ? $promo->ends_at->format('d/m') : '∞' }}
                            </span>
                        @endif
                    </div>

                    <div class="promo-card-footer">
                        <button wire:click="toggleActive({{ $promo->id }})" class="promo-toggle {{ $promo->is_active ? 'promo-toggle-on' : '' }}"></button>
                        <button wire:click="edit({{ $promo->id }})" class="btn-edit-promo">✏️ Editar</button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="promo-empty">
            <span>🎉</span>
            <p>No hay promociones registradas.</p>
            <p style="font-size: 0.75rem; margin-top: 0.5rem; color: #555;">Crea tu primera promoción para empezar a atraer más clientes.</p>
        </div>
    @endif

    {{-- Modal --}}
    @if($showModal)
    <div class="promo-modal-overlay">
        <div class="promo-modal">
            <div class="promo-modal-header">
                <h3>{{ $isEdit ? 'Editar Promoción' : 'Nueva Promoción' }}</h3>
                <button wire:click="$set('showModal', false)" style="background:transparent;border:1px solid #222;color:#666;width:36px;height:36px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    ✕
                </button>
            </div>
            <div class="promo-modal-body">
                <div class="promo-form-group">
                    <label class="promo-form-label">Nombre de la Promoción</label>
                    <input wire:model="name" type="text" class="promo-form-input" placeholder="Ej: 2x1 en alitas clásicas">
                    @error('name') <p class="promo-error">{{ $message }}</p> @enderror
                </div>

                <div class="promo-form-group">
                    <label class="promo-form-label">Descripción (Opcional)</label>
                    <textarea wire:model="description" class="promo-form-textarea" placeholder="Descripción breve..."></textarea>
                </div>

                <div class="promo-form-row">
                    <div class="promo-form-group">
                        <label class="promo-form-label">Sucursal</label>
                        <select wire:model="branch_id" class="promo-form-select">
                            <option value="">🌐 Todas las sucursales</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="promo-form-group">
                        <label class="promo-form-label">Tipo de Promoción</label>
                        <select wire:model.live="type" class="promo-form-select">
                            <option value="discount">💰 Descuento</option>
                            <option value="combo">📦 Combo</option>
                            <option value="birthday">🎂 Cumpleaños</option>
                            <option value="free_item">🎁 Artículo Gratis</option>
                            <option value="custom">⚙️ Personalizada</option>
                        </select>
                        @error('type') <p class="promo-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="promo-form-row">
                    <div class="promo-form-group">
                        <label class="promo-form-label">Tipo de Descuento</label>
                        <select wire:model.live="discount_type" class="promo-form-select">
                            <option value="percentage">% Porcentaje</option>
                            <option value="fixed">$ Monto Fijo</option>
                            <option value="free_item">🎁 Artículo Gratis</option>
                        </select>
                    </div>
                    <div class="promo-form-group">
                        <label class="promo-form-label">Valor del Descuento</label>
                        <input wire:model="discount_value" type="number" step="0.01" min="0" class="promo-form-input" placeholder="Ej: 15">
                    </div>
                </div>

                @if($discount_type === 'free_item')
                <div class="promo-form-row">
                    <div class="promo-form-group">
                        <label class="promo-form-label">Variante Gratis</label>
                        <select wire:model="free_product_variant_id" class="promo-form-select">
                            <option value="">Seleccionar...</option>
                            @foreach($variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->product->name ?? '' }} — {{ $variant->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="promo-form-group">
                        <label class="promo-form-label">Cantidad Gratis</label>
                        <input wire:model="free_quantity" type="number" min="1" class="promo-form-input" placeholder="1">
                    </div>
                </div>
                @endif

                <div class="promo-form-group">
                    <label class="promo-form-label">Pedido Mínimo ($) — Condición opcional</label>
                    <input wire:model="conditions_min_order" type="number" step="0.01" min="0" class="promo-form-input" placeholder="Ej: 200.00 (dejar vacío = sin mínimo)">
                </div>

                <div class="promo-form-row">
                    <div class="promo-form-group">
                        <label class="promo-form-label">Fecha Inicio</label>
                        <input wire:model="starts_at" type="date" class="promo-form-input">
                    </div>
                    <div class="promo-form-group">
                        <label class="promo-form-label">Fecha Fin</label>
                        <input wire:model="ends_at" type="date" class="promo-form-input">
                    </div>
                </div>

                <div class="promo-form-group">
                    <div class="promo-form-check">
                        <input type="checkbox" wire:model="is_active" id="promo-active">
                        <label for="promo-active">Activa</label>
                    </div>
                </div>
            </div>
            <div class="promo-modal-footer">
                <button wire:click="$set('showModal', false)" class="btn-modal-cancel">Cancelar</button>
                <button wire:click="save" class="btn-modal-save">{{ $isEdit ? 'Actualizar' : 'Crear Promoción' }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
