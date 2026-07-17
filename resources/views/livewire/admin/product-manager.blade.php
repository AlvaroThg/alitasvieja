<div class="product-manager-container">
    <style>
        [x-cloak] { display: none !important; }
        .pm-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .pm-title { color: var(--text-strong); font-size: 1.5rem; font-weight: 800; }
        .btn-add { background: linear-gradient(135deg, #dc2626, #b91c1c); color: var(--text-strong); padding: 0.6rem 1.25rem; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; }
        
        .pm-table { width: 100%; border-collapse: collapse; background: var(--bg-surface); border-radius: 12px; overflow: hidden; }
        .pm-table th, .pm-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); color: var(--text); }
        .pm-table th { background: var(--bg-elevated); color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; font-weight: 600; }
        
        .pm-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 100; }
        .pm-modal-content { background: var(--bg-surface); border: 1px solid var(--border-strong); border-radius: 20px; padding: 2rem; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem; }
        .form-input, .form-select { width: 100%; background: var(--bg-base); border: 1px solid var(--border-strong); color: var(--text-strong); padding: 0.75rem; border-radius: 10px; }
        .form-checkbox { display: flex; align-items: center; gap: 0.5rem; color: var(--text-strong); font-size: 0.9rem; cursor: pointer; }
        
        .variants-section { border-top: 1px solid var(--border-strong); padding-top: 1.5rem; margin-top: 1.5rem; }
        .variant-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; background: var(--bg-elevated); padding: 0.5rem; border-radius: 10px; }
        .btn-remove { background: transparent; color: #ef4444; border: 1px solid #ef4444; padding: 0.5rem; border-radius: 8px; cursor: pointer; }
        .btn-add-variant { background: var(--border); color: var(--text-strong); border: 1px solid var(--text-faint); padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.85rem; margin-bottom: 1rem; }
        
        .modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-strong); padding-top: 1.5rem; }
        .btn-cancel { background: transparent; color: var(--text-secondary); border: 1px solid var(--text-faint); padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer; }
        .btn-save { background: #f97316; color: var(--text-strong); border: none; padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer; font-weight: 700; }
        
        .badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 5px; font-size: 0.7rem; background: var(--border-strong); margin-right: 0.3rem; }
        .badge.wings { background: #dc2626; color: var(--text-strong); }
        .badge.stock { background: #f97316; color: var(--text-strong); }
        .badge.sauces { background: #8b5cf6; color: var(--text-strong); }
    </style>

    <div class="pm-header">
        <h2 class="pm-title">Gestión de Productos</h2>
        <button wire:click="create" class="btn-add">+ Nuevo Producto</button>
    </div>

    @if(session()->has('message'))
        <div style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; margin-bottom: 1rem;">{{ session('message') }}</div>
    @endif
    @if(session()->has('error'))
        <div style="background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.3); color: #f87171; padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; margin-bottom: 1rem;">{{ session('error') }}</div>
    @endif

    <table class="pm-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Atributos</th>
                <th>Variantes</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td style="font-weight: 600;">{{ $product->name }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td>
                    @if($product->is_wings) <span class="badge wings">Alitas</span> @endif
                    @if($product->tracks_stock) <span class="badge stock">Inventario</span> @endif
                    @if($product->has_sauces) <span class="badge sauces">Salsas</span> @endif
                </td>
                <td>{{ $product->variants->count() }}</td>
                <td>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button wire:click="edit({{ $product->id }})" style="background: transparent; border: none; color: #f97316; cursor: pointer; text-decoration: underline;">Editar</button>
                        <button wire:click="confirmDeleteProduct({{ $product->id }})" title="Eliminar" style="background: transparent; border: none; color: #ef4444; cursor: pointer; display: inline-flex; align-items: center;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($showModal)
    <div class="pm-modal">
        <div class="pm-modal-content">
            <h3 style="color: var(--text-strong); margin-bottom: 1.5rem; font-size: 1.25rem;">{{ $isEdit ? 'Editar Producto' : 'Nuevo Producto' }}</h3>
            
            <div class="form-grid">
                <div>
                    <div class="form-group">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" wire:model="name" class="form-input">
                        @error('name') <p style="color: #f87171; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select wire:model="category_id" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <input type="text" wire:model="description" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="margin-bottom: 1rem;">Configuraciones</label>
                    <label class="form-checkbox" style="margin-bottom: 0.75rem;">
                        <input type="checkbox" wire:model.live="is_wings">
                        Es un producto de alitas
                    </label>
                    <label class="form-checkbox" style="margin-bottom: 0.75rem;">
                        <input type="checkbox" wire:model="tracks_stock">
                        Llevar control de stock
                    </label>
                    <label class="form-checkbox" style="margin-bottom: 0.75rem;">
                        <input type="checkbox" wire:model="has_sauces">
                        Permite elegir salsas
                    </label>
                    <label class="form-checkbox">
                        <input type="checkbox" wire:model="is_active">
                        Activo (visible en el menú)
                    </label>
                </div>
            </div>

            <div class="variants-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <h4 style="color: var(--text-strong);">Precio y Variantes</h4>
                    <button wire:click="addVariant" class="btn-add-variant">+ Agregar Variante</button>
                </div>
                <p style="color: var(--text-muted); font-size: 0.78rem; margin-bottom: 1rem;">
                    Si es un producto simple (un solo precio), deja <strong>una sola fila</strong> y el Nombre en blanco.
                    Agrega más filas solo si tiene variantes (ej. "5 Piezas", "10 Piezas").
                </p>
                
                @php
                    // Las columnas Piezas y Max Salsas solo aplican a productos de alitas.
                    $gridCols = $is_wings
                        ? '2fr 1fr 1fr 1fr' . str_repeat(' 1fr', count($branches)) . ' auto'
                        : '2fr 1fr' . str_repeat(' 1fr', count($branches)) . ' auto';
                @endphp
                <div style="overflow-x: auto;">
                    @if(count($variants) > 0)
                    <div style="display: grid; grid-template-columns: {{ $gridCols }}; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span class="form-label">Nombre</span>
                        @if($is_wings)
                            <span class="form-label">Piezas</span>
                            <span class="form-label">Max Salsas</span>
                        @endif
                        <span class="form-label" style="color: #f97316; position: relative; display: inline-flex; align-items: center; gap: 4px;" x-data="{ open: false }">
                            Precio general
                            <button type="button" @click="open = !open" style="background: var(--border-strong); color: var(--text-strong); border: none; width: 15px; height: 15px; border-radius: 50%; font-size: 0.6rem; font-weight: 700; cursor: pointer; line-height: 1; flex-shrink: 0;">?</button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                 style="position: absolute; top: 130%; left: 0; z-index: 70; background: var(--bg-elevated); border: 1px solid var(--border-strong); border-radius: 10px; padding: 0.65rem 0.8rem; width: 240px; font-size: 0.72rem; color: var(--text-secondary); font-weight: 400; text-transform: none; letter-spacing: normal; line-height: 1.4; box-shadow: 0 8px 24px rgba(0,0,0,0.35);">
                                Es el precio que se usa cuando una sucursal <strong>no tiene precio propio</strong>. Si pones un precio por sucursal, ese manda.
                            </div>
                        </span>
                        @foreach($branches as $b)
                            <span class="form-label" style="color: #38bdf8;">{{ $b->name }}</span>
                        @endforeach
                        <span></span>
                    </div>
                    @endif

                    @foreach($variants as $index => $variant)
                    <div wire:key="variant-{{ $variant['id'] ?? 'new-'.$index }}" style="display: grid; grid-template-columns: {{ $gridCols }}; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; background: var(--bg-elevated); padding: 0.5rem; border-radius: 10px;">
                        <input type="text" wire:model="variants.{{ $index }}.name" class="form-input" placeholder="Opcional (ej. 10 Piezas)">
                        @if($is_wings)
                            <input type="number" wire:model="variants.{{ $index }}.wings_count" class="form-input" placeholder="0">
                            <input type="number" wire:model="variants.{{ $index }}.max_sauces" class="form-input" placeholder="0">
                        @endif
                        <input type="number" step="0.01" wire:model="variants.{{ $index }}.price" class="form-input" placeholder="0.00" style="border-color: #f97316;">

                        @foreach($branches as $b)
                            <input type="number" step="0.01" wire:model="variants.{{ $index }}.branch_prices.{{ $b->id }}" class="form-input" placeholder="0.00" style="border-color: #38bdf8;">
                        @endforeach

                        <button wire:click="removeVariant({{ $index }})" class="btn-remove">X</button>
                    </div>
                    @error('variants.'.$index.'.price')
                        <p style="color: #f87171; font-size: 0.75rem; margin: -0.25rem 0 0.5rem 0;">{{ $message }}</p>
                    @enderror
                    @foreach($branches as $b)
                        @error('variants.'.$index.'.branch_prices.'.$b->id)
                            <p style="color: #f87171; font-size: 0.75rem; margin: -0.25rem 0 0.5rem 0;">{{ $message }}</p>
                        @enderror
                    @endforeach
                    @endforeach
                </div>
            </div>

            <div class="modal-actions">
                <button wire:click="$set('showModal', false)" class="btn-cancel">Cancelar</button>
                <button wire:click="save" class="btn-save">Guardar Producto</button>
            </div>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="pm-modal">
        <div class="pm-modal-content" style="max-width: 420px;">
            <h3 style="color: var(--text-strong); margin-bottom: 0.75rem; font-size: 1.2rem;">Eliminar producto</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
                ¿Seguro que quieres eliminar <strong>"{{ $deleteProductName }}"</strong>? Esta acción no se puede deshacer.
            </p>
            <div class="modal-actions">
                <button wire:click="$set('showDeleteModal', false)" class="btn-cancel">Cancelar</button>
                <button wire:click="deleteProduct" class="btn-save" style="background: #ef4444;">Sí, eliminar</button>
            </div>
        </div>
    </div>
    @endif
</div>
