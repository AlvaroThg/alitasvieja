<div class="cash-manager-container">
    <style>
        .cash-card {
            background: linear-gradient(145deg, var(--bg-surface), var(--bg-elevated));
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .cash-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #dc2626, #f97316, #dc2626);
            border-radius: 20px 20px 0 0;
        }
        .cash-title {
            color: var(--text-strong);
            font-size: 1.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .cash-form-group {
            margin-bottom: 1.25rem;
        }
        .cash-label {
            display: block;
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .cash-input, .cash-select {
            width: 100%;
            background: var(--bg-surface);
            border: 1px solid var(--border-strong);
            color: var(--text-strong);
            padding: 0.85rem;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .cash-input:focus, .cash-select:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
        }
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: var(--text-strong);
            font-weight: 800;
            text-transform: uppercase;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            transform: translateY(-2px);
        }
        .movements-list {
            margin-top: 2rem;
            border-top: 1px solid var(--border-strong);
            padding-top: 1.5rem;
        }
        .movement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-surface);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            border-left: 4px solid var(--border-strong);
        }
        .movement-item.income {
            border-left-color: #22c55e;
        }
        .movement-item.expense {
            border-left-color: #ef4444;
        }
        .movement-details h4 {
            color: var(--text-strong);
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        .movement-details p {
            color: var(--text-muted);
            font-size: 0.8rem;
        }
        .movement-amount {
            font-weight: 800;
            font-size: 1.1rem;
        }
        .movement-amount.income {
            color: #22c55e;
        }
        .movement-amount.expense {
            color: #ef4444;
        }
        .error-message {
            color: #ef4444;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            display: block;
        }
    </style>

    @if(!$session)
        <!-- Apertura de Caja -->
        <div class="cash-card">
            <h2 class="cash-title">Apertura de Caja</h2>
            <form wire:submit.prevent="openSession">
                <div class="cash-form-group">
                    <label class="cash-label">Monto Inicial ($)</label>
                    <input type="number" step="0.01" wire:model="opening_amount" class="cash-input" placeholder="Ej. 1500.00">
                    @error('opening_amount') <span class="error-message">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn-submit">Abrir Caja</button>
            </form>
        </div>
    @else
        <!-- Gestión de Movimientos -->
        <div class="cash-card" style="max-width: 800px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 class="cash-title" style="margin-bottom: 0;">Caja Activa</h2>
                <span style="color: var(--text-secondary); font-size: 0.9rem;">
                    Abierta por: {{ $session->opener->name ?? 'Usuario' }} | Monto Inicial: ${{ number_format($session->opening_amount, 2) }}
                </span>
            </div>

            {{-- ═══ CAJA CHICA ═══ --}}
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; background: var(--bg-base); border: 1px solid var(--border); border-radius: 14px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">Caja Chica</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: {{ $pettyBalance > 0 ? '#22c55e' : 'var(--text-muted)' }};">${{ number_format($pettyBalance, 2) }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted); max-width: 320px;">Los egresos se pagan de aquí. Si no alcanza, se repone automáticamente desde la Caja de Venta.</div>
                </div>
                <form wire:submit.prevent="loadPettyCash" style="display: flex; gap: 0.5rem; align-items: flex-end;">
                    <div class="cash-form-group" style="margin: 0;">
                        <label class="cash-label" style="font-size: 0.7rem;">Cargar a Caja Chica ($)</label>
                        <input type="number" step="0.01" wire:model="petty_amount" class="cash-input" placeholder="0.00" style="max-width: 150px;">
                        @error('petty_amount') <span class="error-message">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn-submit" style="margin-top: 0; white-space: nowrap;">Cargar</button>
                </form>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Formulario -->
                <div>
                    <h3 style="color: var(--text-strong); margin-bottom: 1rem; font-size: 1.1rem;">Nuevo Movimiento</h3>
                    <form wire:submit.prevent="addMovement">
                        <div class="cash-form-group">
                            <label class="cash-label">Tipo de Movimiento</label>
                            <select wire:model="type" class="cash-select">
                                <option value="income">Entrada (Ingreso a Caja de Venta)</option>
                                <option value="expense">Salida / Egreso (desde Caja Chica)</option>
                            </select>
                            @error('type') <span class="error-message">{{ $message }}</span> @enderror
                        </div>

                        <div class="cash-form-group">
                            <label class="cash-label">Monto ($)</label>
                            <input type="number" step="0.01" wire:model="amount" class="cash-input" placeholder="0.00">
                            @error('amount') <span class="error-message">{{ $message }}</span> @enderror
                        </div>

                        <div class="cash-form-group">
                            <label class="cash-label">Concepto</label>
                            <input type="text" wire:model="concept" class="cash-input" placeholder="Ej. Pago a proveedor">
                            @error('concept') <span class="error-message">{{ $message }}</span> @enderror
                        </div>

                        <div class="cash-form-group">
                            <label class="cash-label">Referencia (Opcional)</label>
                            <input type="text" wire:model="reference" class="cash-input" placeholder="Ej. Factura #123">
                            @error('reference') <span class="error-message">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="btn-submit" style="margin-top: 0;">Registrar Movimiento</button>
                    </form>
                </div>

                <!-- Lista -->
                <div>
                    <h3 style="color: var(--text-strong); margin-bottom: 1rem; font-size: 1.1rem;">Últimos Movimientos</h3>
                    <div style="max-height: 400px; overflow-y: auto; padding-right: 0.5rem;">
                        @forelse($movements as $mov)
                            <div class="movement-item {{ $mov->type }}">
                                <div class="movement-details">
                                    <h4>{{ $mov->concept }}
                                        @if($mov->cash_box === 'petty')
                                            <span style="font-size:0.6rem; font-weight:700; color:#a78bfa; border:1px solid rgba(167,139,250,0.4); border-radius:6px; padding:0.05rem 0.3rem; margin-left:0.25rem;">CAJA CHICA</span>
                                        @elseif($mov->cash_box === 'transfer')
                                            <span style="font-size:0.6rem; font-weight:700; color:#60a5fa; border:1px solid rgba(96,165,250,0.4); border-radius:6px; padding:0.05rem 0.3rem; margin-left:0.25rem;">TRASPASO</span>
                                        @endif
                                    </h4>
                                    <p>{{ $mov->created_at->format('H:i') }} - {{ $mov->reference ?? 'Sin ref.' }}</p>
                                </div>
                                <div class="movement-amount {{ $mov->type }}">
                                    {{ $mov->type === 'income' ? '+' : '-' }} ${{ number_format($mov->amount, 2) }}
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-muted); padding: 2rem 0;">
                                No hay movimientos registrados
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
