<div class="category-manager-container">
    <style>
        .cm-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .cm-title { color: var(--text-strong); font-size: 1.5rem; font-weight: 800; }
        .btn-add { background: linear-gradient(135deg, #10b981, #047857); color: var(--text-strong); padding: 0.6rem 1.25rem; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; }
        
        .cm-table { width: 100%; border-collapse: collapse; background: var(--bg-surface); border-radius: 12px; overflow: hidden; }
        .cm-table th, .cm-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); color: var(--text); }
        .cm-table th { background: var(--bg-elevated); color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; font-weight: 600; }
        
        .cm-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 100; }
        .cm-modal-content { background: var(--bg-surface); border: 1px solid var(--border-strong); border-radius: 20px; padding: 2rem; width: 100%; max-width: 500px; }
        
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem; }
        .form-input { width: 100%; background: var(--bg-base); border: 1px solid var(--border-strong); color: var(--text-strong); padding: 0.75rem; border-radius: 10px; }
        .form-checkbox { display: flex; align-items: center; gap: 0.5rem; color: var(--text-strong); font-size: 0.9rem; cursor: pointer; }
        
        .modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-strong); padding-top: 1.5rem; }
        .btn-cancel { background: transparent; color: var(--text-secondary); border: 1px solid var(--text-faint); padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer; }
        .btn-save { background: #10b981; color: var(--text-strong); border: none; padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer; font-weight: 700; }
        
        .badge-active { background: #10b981; color: var(--text-strong); padding: 0.2rem 0.5rem; border-radius: 5px; font-size: 0.75rem; }
        .badge-inactive { background: #dc2626; color: var(--text-strong); padding: 0.2rem 0.5rem; border-radius: 5px; font-size: 0.75rem; }
    </style>

    <div class="cm-header">
        <h2 class="cm-title">Gestión de Categorías</h2>
        <button wire:click="create" class="btn-add">+ Nueva Categoría</button>
    </div>

    <table class="cm-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $cat)
            <tr>
                <td style="font-weight: 600;">{{ $cat->name }}</td>
                <td>{{ $cat->description ?: 'N/A' }}</td>
                <td>
                    @if($cat->is_active)
                        <span class="badge-active">Activo</span>
                    @else
                        <span class="badge-inactive">Inactivo</span>
                    @endif
                </td>
                <td>
                    <button wire:click="edit({{ $cat->id }})" style="background: transparent; border: none; color: #10b981; cursor: pointer; text-decoration: underline;">Editar</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($showModal)
    <div class="cm-modal">
        <div class="cm-modal-content">
            <h3 style="color: var(--text-strong); margin-bottom: 1.5rem; font-size: 1.25rem;">{{ $isEdit ? 'Editar Categoría' : 'Nueva Categoría' }}</h3>
            
            <div class="form-group">
                <label class="form-label">Nombre de la Categoría</label>
                <input type="text" wire:model="name" class="form-input" placeholder="Ej. Bebidas, Helados, etc.">
            </div>
            
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <input type="text" wire:model="description" class="form-input">
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label class="form-checkbox">
                    <input type="checkbox" wire:model="is_active">
                    Categoría Activa (Visible)
                </label>
            </div>

            <div class="modal-actions">
                <button wire:click="$set('showModal', false)" class="btn-cancel">Cancelar</button>
                <button wire:click="save" class="btn-save">Guardar Categoría</button>
            </div>
        </div>
    </div>
    @endif
</div>
