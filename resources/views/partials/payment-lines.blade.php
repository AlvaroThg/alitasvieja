{{--
    Líneas de cobro: un renglón por método de pago.
    Compartido entre el cobro de mesa y el de pedido de cocina.
    Requiere el trait App\Livewire\Concerns\HandlesSplitPayments en el componente.
--}}
@php
    $metodos = ['cash' => 'Efectivo', 'qr' => 'QR', 'card' => 'Tarjeta', 'transfer' => 'Transferencia'];
@endphp

<div style="margin-bottom: 1rem;">
    <label style="display:block; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.5rem;">
        Método de pago
    </label>

    @foreach($pagos as $i => $pago)
        <div wire:key="pago-{{ $i }}" style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
            <select wire:model.live="pagos.{{ $i }}.method"
                    style="flex: 1.3; background: var(--bg-base); border: 1px solid var(--border); color: var(--text-strong); padding: 0.65rem 0.8rem; border-radius: 11px; font-size: 0.9rem; outline: none; font-family: inherit;">
                @foreach($metodos as $valor => $etiqueta)
                    <option value="{{ $valor }}">{{ $etiqueta }}</option>
                @endforeach
            </select>

            <input type="number" step="0.01" min="0" inputmode="decimal"
                   wire:model.live.debounce.400ms="pagos.{{ $i }}.amount"
                   placeholder="0.00"
                   style="flex: 1; width: 100%; background: var(--bg-base); border: 1px solid var(--border); color: var(--text-strong); padding: 0.65rem 0.8rem; border-radius: 11px; font-size: 0.95rem; font-weight: 700; text-align: right; outline: none; font-family: inherit;">

            @if(count($pagos) > 1)
                <button type="button" wire:click="quitarPago({{ $i }})" title="Quitar este pago"
                        style="flex-shrink: 0; background: transparent; border: 1px solid rgba(239,68,68,0.35); color: #ef4444; width: 2.3rem; height: 2.3rem; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            @endif
        </div>
    @endforeach

    <button type="button" wire:click="agregarPago"
            style="background: transparent; border: 1px dashed var(--border-strong); color: var(--text-secondary); padding: 0.5rem 0.9rem; border-radius: 10px; font-size: 0.8rem; font-weight: 700; cursor: pointer; width: 100%; margin-top: 0.15rem;">
        + Pagó con otro método
    </button>

    {{-- Estado del cobro: mientras no cuadre, el botón de confirmar queda bloqueado. --}}
    @php $falta = $this->faltante; @endphp
    <div style="margin-top: 0.8rem; padding: 0.6rem 0.85rem; border-radius: 10px; font-size: 0.85rem; font-weight: 700;
                background: {{ $this->pagoCubierto ? 'rgba(34,197,94,0.1)' : 'rgba(249,115,22,0.1)' }};
                border: 1px solid {{ $this->pagoCubierto ? 'rgba(34,197,94,0.35)' : 'rgba(249,115,22,0.35)' }};
                color: {{ $this->pagoCubierto ? '#22c55e' : '#f97316' }};">
        @if($this->pagoCubierto)
            Cobro completo
        @elseif($falta > 0)
            Falta cobrar Bs. {{ number_format($falta, 2) }}
        @else
            Se cargó Bs. {{ number_format(abs($falta), 2) }} de más
        @endif
    </div>

    @if($paymentError)
        <div style="margin-top: 0.6rem; padding: 0.6rem 0.85rem; border-radius: 10px; font-size: 0.82rem; font-weight: 600; background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.35); color: #f87171;">
            {{ $paymentError }}
        </div>
    @endif
</div>