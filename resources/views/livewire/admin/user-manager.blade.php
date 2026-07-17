<div class="user-manager-container">
    <style>
        .um-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .um-title { color: var(--text-strong); font-size: 1.5rem; font-weight: 800; }
        .btn-add { background: linear-gradient(135deg, #3b82f6, #2563eb); color: var(--text-strong); padding: 0.6rem 1.25rem; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; }
        
        .um-table { width: 100%; border-collapse: collapse; background: var(--bg-surface); border-radius: 12px; overflow: hidden; }
        .um-table th, .um-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); color: var(--text); }
        .um-table th { background: var(--bg-elevated); color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; font-weight: 600; }
        
        .um-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 100; }
        .um-modal-content { background: var(--bg-surface); border: 1px solid var(--border-strong); border-radius: 20px; padding: 2rem; width: 100%; max-width: 500px; }
        
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem; }
        .form-input, .form-select { width: 100%; background: var(--bg-base); border: 1px solid var(--border-strong); color: var(--text-strong); padding: 0.75rem; border-radius: 10px; }
        
        .modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-strong); padding-top: 1.5rem; }
        .btn-cancel { background: transparent; color: var(--text-secondary); border: 1px solid var(--text-faint); padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer; }
        .btn-save { background: #3b82f6; color: var(--text-strong); border: none; padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer; font-weight: 700; }
        
        .badge-owner { background: #f97316; color: var(--text-strong); padding: 0.2rem 0.5rem; border-radius: 5px; font-size: 0.75rem; }
        .badge-cashier { background: #3b82f6; color: var(--text-strong); padding: 0.2rem 0.5rem; border-radius: 5px; font-size: 0.75rem; }
    </style>

    <div class="um-header">
        <h2 class="um-title">Gestión de Usuarios</h2>
        <button wire:click="create" class="btn-add">+ Nuevo Usuario</button>
    </div>

    <table class="um-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Sucursal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td style="font-weight: 600;">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->role === 'owner')
                        <span class="badge-owner">Administrador</span>
                    @else
                        <span class="badge-cashier">Cajero</span>
                    @endif
                </td>
                <td>{{ $user->branch->name ?? 'N/A' }}</td>
                <td>
                    <button wire:click="edit({{ $user->id }})" style="background: transparent; border: none; color: #3b82f6; cursor: pointer; text-decoration: underline;">Editar</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($showModal)
    <div class="um-modal">
        <div class="um-modal-content">
            <h3 style="color: var(--text-strong); margin-bottom: 1.5rem; font-size: 1.25rem;">{{ $isEdit ? 'Editar Usuario' : 'Nuevo Usuario' }}</h3>
            
            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input type="text" wire:model="name" class="form-input" placeholder="Ej. Juan Pérez">
                @error('name') <span style="color: red; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">Email (Usuario de acceso)</label>
                <input type="email" wire:model="email" class="form-input" placeholder="ejemplo@alitasvega.com">
                @error('email') <span style="color: red; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña {{ $isEdit ? '(Dejar en blanco para no cambiar)' : '' }}</label>
                <input type="password" wire:model="password" class="form-input" placeholder="********">
                @error('password') <span style="color: red; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">Rol</label>
                <select wire:model="role" class="form-select">
                    <option value="cashier">Cajero (Solo POS)</option>
                    <option value="owner">Administrador (Acceso Total)</option>
                </select>
                @error('role') <span style="color: red; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Sucursal</label>
                <select wire:model="branch_id" class="form-select">
                    <option value="">Seleccione...</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id') <span style="color: red; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="modal-actions">
                <button wire:click="$set('showModal', false)" class="btn-cancel">Cancelar</button>
                <button wire:click="save" class="btn-save">Guardar Usuario</button>
            </div>
        </div>
    </div>
    @endif
</div>
