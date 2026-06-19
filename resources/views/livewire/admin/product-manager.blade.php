<div class="product-manager-container">
    <style>
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
                    <button wire:click="edit({{ $product->id }})" style="background: transparent; border: none; color: #f97316; cursor: pointer; text-decoration: underline;">Editar</button>
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="color: var(--text-strong);">Variantes y Precios por Sucursal</h4>
                    <button wire:click="addVariant" class="btn-add-variant">+ Agregar Variante</button>
                </div>
                
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
                        <span class="form-label" style="color: #f97316;">Precio Base</span>
                        @foreach($branches as $b)
                            <span class="form-label" style="color: #38bdf8;">{{ $b->name }}</span>
                        @endforeach
                        <span></span>
                    </div>
                    @endif

                    @foreach($variants as $index => $variant)
                    <div wire:key="variant-{{ $variant['id'] ?? 'new-'.$index }}" style="display: grid; grid-template-columns: {{ $gridCols }}; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; background: var(--bg-elevated); padding: 0.5rem; border-radius: 10px;">
                        <input type="text" wire:model="variants.{{ $index }}.name" class="form-input" placeholder="Ej. 10 Piezas">
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
</div>
