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
        
        .action-link { background: transparent; border: none; color: #10b981; cursor: pointer; text-decoration: underline; margin-right: 0.5rem; }
        .action-link.danger { color: #dc2626; }
    </style>

    <div class="cm-header">
        <div>
            <h2 class="cm-title">Salsas</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.35rem; max-width: 560px;">
                Gestiona las salsas disponibles en el restaurante y configura el precio de recargo por bañadas para cada sucursal.
            </p>
        </div>
        <button wire:click="create" class="btn-add">+ Nueva Salsa</button>
    </div>

    {{-- Configuración de Recargos por Sucursal --}}
    <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 1.25rem 1.5rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
            <div style="width: 38px; height: 38px; background: rgba(220, 38, 38, 0.15); border: 1px solid rgba(220, 38, 38, 0.3); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                🌶️
            </div>
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-strong);">Recargo por Salsas Bañadas por Sucursal</h3>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">
                    Configura el monto (Bs.) que se adicionará automáticamente a cada porción cuando el cliente elija alitas o picadas <strong>bañadas</strong>.
                </p>
            </div>
        </div>

        @if(session()->has('success_branch'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; padding: 0.65rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1rem;">
                ✓ {{ session('success_branch') }}
            </div>
        @endif

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem;">
            @foreach($branches as $b)
                <div style="background: var(--bg-base); border: 1px solid var(--border-strong); border-radius: 12px; padding: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                    <div>
                        <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-strong);">{{ $b->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $b->city ?? 'Sucursal' }}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="position: relative; width: 110px;">
                            <span style="position: absolute; left: 0.6rem; top: 50%; transform: translateY(-50%); font-weight: 800; font-size: 0.78rem; color: var(--text-muted);">Bs.</span>
                            <input type="number" step="0.5" min="0" 
                                   wire:model.live="branchCoatedPrices.{{ $b->id }}"
                                   style="width: 100%; background: var(--bg-surface); border: 1px solid var(--border-strong); color: var(--text-strong); font-weight: 800; font-size: 0.9rem; padding: 0.45rem 0.5rem 0.45rem 2.1rem; border-radius: 8px; outline: none;">
                        </div>
                        <button type="button" wire:click="saveBranchCoatedPrice({{ $b->id }})"
                                style="background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; font-weight: 800; font-size: 0.78rem; padding: 0.45rem 0.75rem; border-radius: 8px; border: none; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                            Guardar
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <table class="cm-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Nivel Picante (0-10)</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sauces as $sauce)
            <tr>
                <td style="font-weight: 600;">{{ $sauce->name }}</td>
                <td>
                    {{ $sauce->spice_level }}
                    @if($sauce->spice_level > 0)
                        <span style="color: #ef4444; margin-left: 0.25rem;">
                            {{ str_repeat('🌶️', min(3, ceil($sauce->spice_level / 3))) }}
                        </span>
                    @endif
                </td>
                <td>
                    @if($sauce->is_active)
                        <span class="badge-active">Activo</span>
                    @else
                        <span class="badge-inactive">Inactivo</span>
                    @endif
                </td>
                <td>
                    <button wire:click="edit({{ $sauce->id }})" class="action-link">Editar</button>
                    <button wire:click="toggleActive({{ $sauce->id }})" class="action-link {{ $sauce->is_active ? 'danger' : '' }}">
                        {{ $sauce->is_active ? 'Desactivar' : 'Activar' }}
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($showModal)
    <div class="cm-modal">
        <div class="cm-modal-content">
            <h3 style="color: var(--text-strong); margin-bottom: 1.5rem; font-size: 1.25rem;">{{ $isEdit ? 'Editar Salsa' : 'Nueva Salsa' }}</h3>
            
            <div class="form-group">
                <label class="form-label">Nombre de la Salsa</label>
                <input type="text" wire:model="name" class="form-input" placeholder="Ej. BBQ Tradicional">
                @error('name') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">Nivel de Picante (0-10)</label>
                <input type="number" wire:model="spice_level" min="0" max="10" class="form-input">
                @error('spice_level') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label class="form-checkbox">
                    <input type="checkbox" wire:model="is_active">
                    Salsa Activa (Visible en el POS)
                </label>
            </div>

            <div class="modal-actions">
                <button wire:click="$set('showModal', false)" class="btn-cancel">Cancelar</button>
                <button wire:click="save" class="btn-save">Guardar Salsa</button>
            </div>
        </div>
    </div>
    @endif
</div>
