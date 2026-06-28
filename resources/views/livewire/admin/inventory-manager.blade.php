<div>
    <style>
        .inv-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .inv-title { font-size: 1.75rem; font-weight: 900; display: inline-flex; align-items: center; gap: 0.6rem; }
        .inv-title-icon { color: #f97316; flex-shrink: 0; }
        .inv-title-text { background: linear-gradient(135deg, #f97316, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .btn-new-inv {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.5rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c); color: var(--text-strong); font-weight: 800;
            font-size: 0.85rem; border: none; border-radius: 14px; cursor: pointer; transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .btn-new-inv:hover { background: linear-gradient(135deg, #ef4444, #dc2626); transform: translateY(-2px); }

        .inv-filters { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .inv-select, .inv-input {
            background: var(--bg-surface); border: 1px solid var(--border); color: var(--text-secondary); padding: 0.6rem 1rem;
            border-radius: 12px; font-size: 0.85rem; font-family: inherit; outline: none; transition: border-color 0.2s;
        }
        .inv-select:focus, .inv-input:focus { border-color: #f97316; }
        .inv-input { flex: 1; min-width: 200px; }

        /* Alert banner */
        .inv-alert-banner {
            background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.25);
            border-radius: 14px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;
            display: flex; align-items: center; gap: 0.75rem;
        }
        .inv-alert-icon { font-size: 1.5rem; }
        .inv-alert-text { font-size: 0.85rem; font-weight: 600; color: #f87171; }
        .inv-alert-count {
            background: #dc2626; color: var(--text-strong); font-weight: 800; font-size: 0.75rem;
            padding: 0.2rem 0.6rem; border-radius: 50px; margin-left: 0.25rem;
        }

        /* Table */
        .inv-table-wrap {
            background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden;
        }
        .inv-table { width: 100%; border-collapse: collapse; }
        .inv-table thead { background: var(--bg-base); }
        .inv-table th {
            padding: 0.85rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 700;
            color: var(--text-faint); text-transform: uppercase; letter-spacing: 0.08em; border-bottom: 1px solid var(--border);
        }
        .inv-table td {
            padding: 0.85rem 1rem; font-size: 0.85rem; color: var(--text-secondary); border-bottom: 1px solid var(--bg-elevated);
            vertical-align: middle;
        }
        .inv-table tbody tr { transition: background 0.15s; }
        .inv-table tbody tr:hover { background: rgba(249, 115, 22, 0.03); }
        .inv-table tbody tr:last-child td { border-bottom: none; }

        .inv-product-name { font-weight: 700; color: var(--text); }
        .inv-variant-name { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem; }
        .inv-branch-tag {
            display: inline-block; background: var(--bg-elevated); border: 1px solid var(--border);
            padding: 0.2rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 600; color: var(--text-muted);
        }

        /* Stock badges */
        .stock-ok { color: #22c55e; font-weight: 800; }
        .stock-low { color: #f97316; font-weight: 800; }
        .stock-critical { color: #dc2626; font-weight: 800; animation: pulse-stock 1.5s ease-in-out infinite; }
        @keyframes pulse-stock { 0%,100%{opacity:1;}50%{opacity:0.5;} }

        .alert-badge { font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 50px; }
        .alert-badge-ok { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .alert-badge-warn { background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.2); }
        .alert-badge-danger { background: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.2); }

        /* Action buttons */
        .inv-actions { display: flex; gap: 0.35rem; justify-content: center; flex-wrap: wrap; }
        .btn-action {
            background: var(--bg-surface); border: 1px solid var(--border); padding: 0.35rem 0.6rem;
            border-radius: 8px; font-size: 0.7rem; font-weight: 700; cursor: pointer; transition: all 0.2s;
        }
        .btn-action:hover { transform: translateY(-1px); }
        .btn-action-adjust { color: #f97316; }
        .btn-action-adjust:hover { border-color: #f97316; background: rgba(249, 115, 22, 0.05); }
        .btn-action-edit { color: #60a5fa; }
        .btn-action-edit:hover { border-color: #60a5fa; background: rgba(96, 165, 250, 0.05); }
        .btn-action-delete { color: #f87171; }
        .btn-action-delete:hover { border-color: #dc2626; background: rgba(220, 38, 38, 0.05); }

        /* Modal */
        .inv-modal-overlay {
            position: fixed; inset: 0; z-index: 50; display: flex; align-items: center;
            justify-content: center; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px);
        }
        .inv-modal {
            background: var(--bg-surface); border: 1px solid var(--border); width: 100%; max-width: 480px;
            border-radius: 20px; overflow: hidden;
        }
        .inv-modal-header {
            padding: 1.25rem 1.5rem; background: var(--bg-base); border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .inv-modal-header h3 { font-size: 1.15rem; font-weight: 800; color: var(--text-strong); }
        .inv-modal-body { padding: 1.25rem 1.5rem; }
        .inv-modal-footer { padding: 1.25rem 1.5rem; border-top: 1px solid var(--border); background: var(--bg-base); display: flex; gap: 0.75rem; justify-content: flex-end; }
        .inv-modal-close {
            background: transparent; border: 1px solid var(--border); color: var(--text-muted); width: 36px; height: 36px;
            border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .inv-modal-close:hover { color: #dc2626; border-color: #dc2626; }

        .inv-form-group { margin-bottom: 1rem; }
        .inv-form-label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; }
        .inv-form-input, .inv-form-select {
            width: 100%; background: var(--bg-base); border: 1px solid var(--border); color: var(--text-strong);
            padding: 0.7rem 1rem; border-radius: 12px; font-size: 0.9rem; outline: none; font-family: inherit;
        }
        .inv-form-input:focus, .inv-form-select:focus { border-color: #f97316; }
        .inv-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

        .btn-modal-cancel {
            padding: 0.7rem 1.5rem; background: var(--bg-elevated); color: var(--text-muted); border: 1px solid var(--border);
            border-radius: 12px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;
        }
        .btn-modal-cancel:hover { color: var(--text-strong); border-color: var(--text-faint); }
        .btn-modal-save {
            padding: 0.7rem 1.5rem; background: linear-gradient(135deg, #dc2626, #b91c1c); color: var(--text-strong);
            border: none; border-radius: 12px; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .btn-modal-save:hover { background: linear-gradient(135deg, #ef4444, #dc2626); transform: translateY(-1px); }
        .btn-modal-delete {
            padding: 0.7rem 1.5rem; background: #dc2626; color: var(--text-strong);
            border: none; border-radius: 12px; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;
        }
        .btn-modal-delete:hover { background: #ef4444; }

        .inv-empty { text-align: center; padding: 3rem; color: var(--text-faint); }
        .inv-empty span { font-size: 3rem; display: block; margin-bottom: 0.75rem; opacity: 0.3; }
        .inv-error { color: #f87171; font-size: 0.75rem; margin-top: 0.25rem; }
        .inv-success {
            background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2);
            color: #22c55e; padding: 0.75rem 1rem; border-radius: 12px; font-size: 0.85rem;
            font-weight: 600; margin-bottom: 1rem;
        }
        .inv-delete-warn {
            background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.2);
            padding: 1rem; border-radius: 12px; text-align: center; margin-bottom: 1rem;
        }
        .inv-delete-warn p { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem; }
        .inv-delete-warn strong { color: #f87171; }

        .inv-pagination { display: flex; justify-content: center; padding: 1rem; }
    </style>

    {{-- Header --}}
    <div class="inv-header">
        <h1 class="inv-title">
            <svg class="inv-title-icon" width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            <span class="inv-title-text">Inventario por Sucursal</span>
        </h1>
        <button wire:click="openCreateModal" class="btn-new-inv">+ Nuevo Registro</button>
    </div>

    @if(session()->has('message'))
        <div class="inv-success">{{ session('message') }}</div>
    @endif

    {{-- Alert banner --}}
    @php
        $alertCount = $inventoryList->filter(fn($i) => $i->minimum_alert > 0 && $i->stock_quantity <= $i->minimum_alert)->count();
    @endphp
    @if($alertCount > 0)
        <div class="inv-alert-banner">
            <span class="inv-alert-icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path></svg></span>
            <span class="inv-alert-text">
                Hay productos con stock bajo o agotado
                <span class="inv-alert-count">{{ $alertCount }}</span>
            </span>
        </div>
    @endif

    {{-- Filters --}}
    <div class="inv-filters">
        <select wire:model.live="branchId" class="inv-select">
            <option value="">Todas las sucursales</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>
        <input wire:model.live.debounce.300ms="search" type="text" class="inv-input" placeholder="Buscar producto...">
    </div>

    {{-- Table --}}
    <div class="inv-table-wrap">
        @if($inventoryList->count() > 0)
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>Producto / Variante</th>
                        <th>Sucursal</th>
                        <th style="text-align:center;">Stock</th>
                        <th style="text-align:center;">Mínimo Alerta</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryList as $inv)
                        <tr>
                            <td>
                                <div class="inv-product-name">{{ $inv->productVariant->product->name ?? '—' }}</div>
                                <div class="inv-variant-name">{{ $inv->productVariant->name ?? '—' }}</div>
                            </td>
                            <td><span class="inv-branch-tag">{{ $inv->branch->name ?? '—' }}</span></td>
                            <td style="text-align:center;">
                                @if($inv->minimum_alert > 0 && $inv->stock_quantity <= 0)
                                    <span class="stock-critical">{{ $inv->stock_quantity }}</span>
                                @elseif($inv->minimum_alert > 0 && $inv->stock_quantity <= $inv->minimum_alert)
                                    <span class="stock-low">{{ $inv->stock_quantity }}</span>
                                @else
                                    <span class="stock-ok">{{ $inv->stock_quantity }}</span>
                                @endif
                            </td>
                            <td style="text-align:center; color: var(--text-muted);">{{ $inv->minimum_alert }}</td>
                            <td style="text-align:center;">
                                @if($inv->minimum_alert > 0 && $inv->stock_quantity <= 0)
                                    <span class="alert-badge alert-badge-danger">Agotado</span>
                                @elseif($inv->minimum_alert > 0 && $inv->stock_quantity <= $inv->minimum_alert)
                                    <span class="alert-badge alert-badge-warn">Bajo</span>
                                @else
                                    <span class="alert-badge alert-badge-ok">OK</span>
                                @endif
                            </td>
                            <td>
                                <div class="inv-actions">
                                    <button wire:click="openAdjustModal({{ $inv->id }})" class="btn-action btn-action-adjust" title="Ajustar Stock">
                                        Ajustar
                                    </button>
                                    <button wire:click="openEditModal({{ $inv->id }})" class="btn-action btn-action-edit" title="Editar">
                                        Editar
                                    </button>
                                    <button wire:click="confirmDelete({{ $inv->id }})" class="btn-action btn-action-delete" title="Eliminar">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="inv-pagination">
                {{ $inventoryList->links() }}
            </div>
        @else
            <div class="inv-empty">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <p>No se encontraron registros de inventario.</p>
                <p style="font-size: 0.75rem; margin-top: 0.5rem; color: var(--text-faint);">
                    Usa el botón "+ Nuevo Registro" para dar de alta un producto en el inventario de una sucursal.
                </p>
            </div>
        @endif
    </div>

    {{-- ═══ MODAL: Crear / Editar Registro ═══ --}}
    @if($showCreateModal)
    <div class="inv-modal-overlay">
        <div class="inv-modal">
            <div class="inv-modal-header">
                <h3>{{ $isEdit ? 'Editar Registro' : 'Nuevo Registro de Inventario' }}</h3>
                <button wire:click="$set('showCreateModal', false)" class="inv-modal-close">✕</button>
            </div>
            <div class="inv-modal-body">
                @if(!$isEdit)
                <div class="inv-form-group">
                    <label class="inv-form-label">Sucursal</label>
                    <select wire:model="formBranchId" class="inv-form-select">
                        <option value="">Seleccionar sucursal...</option>
                        @if(!$isEdit && $isOwner)
                            <option value="all">— Todas las sucursales —</option>
                        @endif
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('formBranchId') <p class="inv-error">{{ $message }}</p> @enderror
                </div>

                <div class="inv-form-group">
                    <label class="inv-form-label">Producto / Variante</label>
                    <select wire:model="formVariantId" class="inv-form-select">
                        <option value="">Seleccionar variante...</option>
                        @foreach($variants as $variant)
                            <option value="{{ $variant->id }}">{{ $variant->product->name ?? '' }} — {{ $variant->name }} (Bs. {{ number_format($variant->price, 2) }})</option>
                        @endforeach
                    </select>
                    @error('formVariantId') <p class="inv-error">{{ $message }}</p> @enderror
                </div>

                <div class="inv-form-row">
                    <div class="inv-form-group">
                        <label class="inv-form-label">Stock Inicial</label>
                        <input wire:model="formStockQuantity" type="number" min="0" class="inv-form-input" placeholder="0">
                        @error('formStockQuantity') <p class="inv-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="inv-form-group">
                        <label class="inv-form-label">Alerta Mínimo</label>
                        <input wire:model="formMinimumAlert" type="number" min="0" class="inv-form-input" placeholder="0">
                        @error('formMinimumAlert') <p class="inv-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                @else
                {{-- Solo editar mínimo de alerta --}}
                <div class="inv-form-group">
                    <label class="inv-form-label">Alerta Mínimo de Stock</label>
                    <input wire:model="formMinimumAlert" type="number" min="0" class="inv-form-input" placeholder="0">
                    @error('formMinimumAlert') <p class="inv-error">{{ $message }}</p> @enderror
                    <p style="color: var(--text-faint); font-size: 0.75rem; margin-top: 0.35rem;">
                        Cuando el stock sea igual o menor a este valor, se mostrará una alerta en la pantalla.
                        <br>Para ajustar la cantidad de stock, usa el botón "Ajustar".
                    </p>
                </div>
                @endif
            </div>
            <div class="inv-modal-footer">
                <button wire:click="$set('showCreateModal', false)" class="btn-modal-cancel">Cancelar</button>
                <button wire:click="saveCreate" class="btn-modal-save">{{ $isEdit ? 'Guardar Cambios' : 'Crear Registro' }}</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ MODAL: Ajustar Stock ═══ --}}
    @if($showAdjustModal)
    <div class="inv-modal-overlay">
        <div class="inv-modal">
            <div class="inv-modal-header">
                <h3>Ajustar Stock</h3>
                <button wire:click="closeAdjustModal" class="inv-modal-close">✕</button>
            </div>
            <div class="inv-modal-body">
                <div class="inv-form-group">
                    <label class="inv-form-label">Tipo de Movimiento</label>
                    <select wire:model="adjustmentType" class="inv-form-select">
                        <option value="in">Entrada (Compra / Reabasto)</option>
                        <option value="out">Salida (Merma / Devolución)</option>
                        <option value="adjustment">Ajuste Manual</option>
                    </select>
                </div>
                <div class="inv-form-group">
                    <label class="inv-form-label">Cantidad</label>
                    <input wire:model="adjustmentQuantity" type="number" min="1" class="inv-form-input" placeholder="Ej: 50">
                    @error('adjustmentQuantity') <p class="inv-error">{{ $message }}</p> @enderror
                </div>
                <div class="inv-form-group">
                    <label class="inv-form-label">Razón / Motivo</label>
                    <input wire:model="adjustmentReason" type="text" class="inv-form-input" placeholder="Ej: Compra semanal de proveedor">
                    @error('adjustmentReason') <p class="inv-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="inv-modal-footer">
                <button wire:click="closeAdjustModal" class="btn-modal-cancel">Cancelar</button>
                <button wire:click="saveAdjustment" class="btn-modal-save">Guardar Ajuste</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ MODAL: Confirmar Eliminación ═══ --}}
    @if($showDeleteModal)
    <div class="inv-modal-overlay">
        <div class="inv-modal">
            <div class="inv-modal-header">
                <h3>Confirmar Eliminación</h3>
                <button wire:click="$set('showDeleteModal', false)" class="inv-modal-close">✕</button>
            </div>
            <div class="inv-modal-body">
                <div class="inv-delete-warn">
                    <p>¿Estás seguro de eliminar el registro de inventario para:</p>
                    <p><strong>{{ $deleteItemName }}</strong></p>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.75rem;">
                        Se eliminarán también todos los movimientos históricos asociados. Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>
            <div class="inv-modal-footer">
                <button wire:click="$set('showDeleteModal', false)" class="btn-modal-cancel">Cancelar</button>
                <button wire:click="deleteInventory" class="btn-modal-delete">Sí, Eliminar</button>
            </div>
        </div>
    </div>
    @endif
</div>