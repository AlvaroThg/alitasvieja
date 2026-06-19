<div>
    <style>
        .table-grid-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .table-grid-header svg {
            color: #dc2626;
        }
        .table-grid-header h2 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .table-grid-header h2 span {
            color: #666;
            font-weight: 500;
            font-size: 1rem;
            margin-left: 0.5rem;
        }
        .table-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        @media (min-width: 768px) { .table-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (min-width: 1024px) { .table-grid { grid-template-columns: repeat(5, 1fr); } }

        .table-card {
            position: relative;
            overflow: hidden;
            padding: 1.5rem;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 140px;
            border: 1px solid transparent;
        }
        .table-card:hover {
            transform: translateY(-4px);
        }
        .table-card:active {
            transform: scale(0.96);
        }
        /* Available */
        .table-available {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-color: rgba(220, 38, 38, 0.5);
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.15);
        }
        .table-available:hover {
            box-shadow: 0 8px 30px rgba(220, 38, 38, 0.3);
        }
        .table-available .table-number {
            color: #fff;
        }
        .table-available .table-status-badge {
            background: rgba(0,0,0,0.2);
            color: rgba(255,255,255,0.9);
        }
        /* Occupied */
        .table-occupied {
            background: #111;
            border-color: #2a2a2a;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .table-occupied:hover {
            border-color: #f97316;
            box-shadow: 0 8px 30px rgba(249, 115, 22, 0.15);
        }
        .table-occupied .table-number {
            color: #f97316;
        }
        .table-occupied .table-status-badge {
            background: rgba(249, 115, 22, 0.1);
            color: #f97316;
            border: 1px solid rgba(249, 115, 22, 0.15);
        }
        /* Reserved */
        .table-reserved {
            background: transparent;
            border: 2px dashed #dc2626;
        }
        .table-reserved:hover {
            background: rgba(220, 38, 38, 0.05);
        }
        .table-reserved .table-number {
            color: #dc2626;
        }
        .table-reserved .table-status-badge {
            background: rgba(220, 38, 38, 0.08);
            color: #dc2626;
        }
        /* Default / other */
        .table-default {
            background: #111;
            border-color: #1e1e1e;
        }
        .table-default:hover {
            border-color: #333;
        }
        .table-default .table-number {
            color: #666;
        }
        .table-default .table-status-badge {
            background: #1a1a1a;
            color: #555;
        }

        .table-number {
            font-size: 2.25rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            z-index: 1;
            letter-spacing: -0.03em;
        }
        .table-status-badge {
            position: relative;
            z-index: 1;
            padding: 0.2rem 0.65rem;
            border-radius: 50px;
            font-size: 0.6rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.1em;
        }
        /* Hover overlay */
        .table-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, transparent 60%);
            border-radius: 16px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .table-card:hover::after {
            opacity: 1;
        }

        /* Modal Styles */
        .table-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(8px);
        }
        .table-modal {
            background: #111;
            border: 1px solid #222;
            width: 100%;
            max-width: 380px;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .table-modal-header {
            padding: 1.25rem 1.5rem;
            background: #0a0a0a;
            border-bottom: 1px solid #222;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-modal-header h3 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
        }
        .table-modal-close {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid #222;
            border-radius: 10px;
            color: #666;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .table-modal-close:hover {
            color: #dc2626;
            border-color: #dc2626;
        }
        .table-modal-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .btn-action {
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #f97316, #dc2626);
            color: #fff;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #fb923c, #ef4444);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
        }
        .btn-secondary {
            background: #1a1a1a;
            color: #fff;
            border: 1px solid #333;
        }
        .btn-secondary:hover {
            background: #252525;
            border-color: #444;
        }
        .btn-warning {
            background: transparent;
            color: #f97316;
            border: 1px solid rgba(249, 115, 22, 0.3);
        }
        .btn-warning:hover {
            background: rgba(249, 115, 22, 0.1);
        }
    </style>

    <div class="table-grid-header">
        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
        <h2>Mapa de Mesas <span>· Selecciona una mesa</span></h2>
    </div>
    
    <div class="table-grid">
        @foreach($tables as $table)
            <div 
                wire:click="manageTable({{ $table->id }})"
                class="table-card 
                @if($table->status === 'available') table-available
                @elseif($table->status === 'occupied') table-occupied
                @elseif($table->status === 'reserved') table-reserved
                @else table-default @endif"
            >
                <span class="table-number">
                    {{ $table->name }}
                </span>
                
                <span class="table-status-badge">
                    {{ function_exists('__') ? __($table->status) : $table->status }}
                </span>
            </div>
        @endforeach
    </div>

    <!-- Modal Acción Mesa -->
    @if($showActionModal && $selectedTableForAction)
    <div class="table-modal-overlay">
        <div class="table-modal">
            <div class="table-modal-header">
                <h3>Opciones de Mesa {{ $selectedTableForAction->name }}</h3>
                <button wire:click="$set('showActionModal', false)" class="table-modal-close">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="table-modal-body">
                @if($selectedTableForAction->status !== 'occupied')
                    <button wire:click="createOrder" class="btn-action btn-primary">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tomar Pedido
                    </button>
                @endif
                
                @if($selectedTableForAction->status === 'occupied')
                    <button wire:click="openCheckout" class="btn-action btn-secondary">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Liberar Mesa (Realizar Pago)
                    </button>
                    <!-- Opcional si permites añadir a orden existente -->
                    <!-- <button wire:click="createOrder" class="btn-action btn-primary">Añadir al Pedido Actual</button> -->
                @endif

                @if($selectedTableForAction->status === 'available')
                    <button wire:click="changeStatus('reserved')" class="btn-action btn-warning">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Marcar como Reservada
                    </button>
                @endif

                @if($selectedTableForAction->status === 'reserved')
                    <button wire:click="changeStatus('available')" class="btn-action btn-secondary">Quitar Reserva</button>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Checkout Rápid -->
    @if($showCheckoutModal && $selectedTableForAction)
    <div class="table-modal-overlay">
        <div class="table-modal" style="max-width: 400px;">
            <div class="table-modal-header">
                <h3>Cobrar Mesa {{ $selectedTableForAction->name }}</h3>
                <button wire:click="$set('showCheckoutModal', false)" class="table-modal-close">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="table-modal-body">
                <div style="background: #1a1a1a; padding: 1rem; border-radius: 10px; text-align: center; margin-bottom: 1rem;">
                    <span style="color: #888; font-size: 0.9rem;">Total a Pagar</span>
                    <div style="font-size: 2rem; font-weight: 800; color: #fff;">Bs {{ number_format($checkoutOrderTotal, 2) }}</div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="color: #aaa; font-size: 0.85rem; margin-bottom: 0.5rem; display: block;">Método de Pago</label>
                    <select wire:model="checkoutPaymentMethod" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: #222; border: 1px solid #333; color: #fff; outline: none;">
                        <option value="cash">Efectivo</option>
                        <option value="card">Tarjeta / POS</option>
                        <option value="qr">Pago QR</option>
                        <option value="transfer">Transferencia</option>
                    </select>
                </div>

                <button wire:click="processCheckout" class="btn-action btn-primary" style="margin-top: 0.5rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Confirmar y Liberar Mesa
                </button>
            </div>
        </div>
    </div>
    @endif
</div>