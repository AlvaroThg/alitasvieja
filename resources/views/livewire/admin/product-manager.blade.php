<div class="product-manager-container">
    <style>
        .pm-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .pm-title { color: #fff; font-size: 1.5rem; font-weight: 800; }
        .btn-add { background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; padding: 0.6rem 1.25rem; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; }
        
        .pm-table { width: 100%; border-collapse: collapse; background: #111; border-radius: 12px; overflow: hidden; }
        .pm-table th, .pm-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #222; color: #eee; }
        .pm-table th { background: #1a1a1a; color: #888; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; }
        
        .pm-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 100; }
        .pm-modal-content { background: #141414; border: 1px solid #333; border-radius: 20px; padding: 2rem; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; color: #aaa; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .form-input, .form-select { width: 100%; background: #0a0a0a; border: 1px solid #333; color: #fff; padding: 0.75rem; border-radius: 10px; }
        .form-checkbox { display: flex; align-items: center; gap: 0.5rem; color: #fff; font-size: 0.9rem; cursor: pointer; }
        
        .variants-section { border-top: 1px solid #333; padding-top: 1.5rem; margin-top: 1.5rem; }
        .variant-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; background: #1a1a1a; padding: 0.5rem; border-radius: 10px; }
        .btn-remove { background: transparent; color: #ef4444; border: 1px solid #ef4444; padding: 0.5rem; border-radius: 8px; cursor: pointer; }
        .btn-add-variant { background: #222; color: #fff; border: 1px solid #444; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.85rem; margin-bottom: 1rem; }
        
        .modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid #333; padding-top: 1.5rem; }
        .btn-cancel { background: transparent; color: #aaa; border: 1px solid #444; padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer; }
        .btn-save { background: #f97316; color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer; font-weight: 700; }
        
        .badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 5px; font-size: 0.7rem; background: #333; margin-right: 0.3rem; }
        .badge.wings { background: #dc2626; color: #fff; }
        .badge.stock { background: #f97316; color: #fff; }
        .badge.sauces { background: #8b5cf6; color: #fff; }
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
            <h3 style="color: #fff; margin-bottom: 1.5rem; font-size: 1.25rem;">{{ $isEdit ? 'Editar Producto' : 'Nuevo Producto' }}</h3>
            
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
                        <input type="checkbox" wire:model="is_wings">
                        Es Alitas (is_wings)
                    </label>
                    <label class="form-checkbox" style="margin-bottom: 0.75rem;">
                        <input type="checkbox" wire:model="tracks_stock">
                        Controla Inventario (tracks_stock)
                    </label>
                    <label class="form-checkbox" style="margin-bottom: 0.75rem;">
                        <input type="checkbox" wire:model="has_sauces">
                        Lleva Salsas (has_sauces)
                    </label>
                    <label class="form-checkbox">
                        <input type="checkbox" wire:model="is_active">
                        Activo
                    </label>
                </div>
            </div>

            <div class="variants-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="color: #fff;">Variantes</h4>
                    <button wire:click="addVariant" class="btn-add-variant">+ Agregar Variante</button>
                </div>
                
                @if(count($variants) > 0)
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span class="form-label">Nombre</span>
                    <span class="form-label">Piezas (Wings)</span>
                    <span class="form-label">Max Salsas</span>
                    <span class="form-label">Precio</span>
                    <span></span>
                </div>
                @endif

                @foreach($variants as $index => $variant)
                <div class="variant-row">
                    <input type="text" wire:model="variants.{{ $index }}.name" class="form-input" placeholder="Ej. 10 Piezas">
                    <input type="number" wire:model="variants.{{ $index }}.wings_count" class="form-input" placeholder="0">
                    <input type="number" wire:model="variants.{{ $index }}.max_sauces" class="form-input" placeholder="0">
                    <input type="number" step="0.01" wire:model="variants.{{ $index }}.price" class="form-input" placeholder="0.00">
                    <button wire:click="removeVariant({{ $index }})" class="btn-remove">X</button>
                </div>
                @endforeach
            </div>

            <div class="modal-actions">
                <button wire:click="$set('showModal', false)" class="btn-cancel">Cancelar</button>
                <button wire:click="save" class="btn-save">Guardar Producto</button>
            </div>
        </div>
    </div>
    @endif
</div>
