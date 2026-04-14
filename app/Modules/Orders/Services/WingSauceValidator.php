<?php

namespace App\Modules\Orders\Services;

use App\Models\Branch;
use Illuminate\Validation\ValidationException;

/**
 * Valida las reglas de negocio de salsas para combos de alitas.
 *
 * REGLAS IMPLEMENTADAS:
 *  1. Salsas bañadas (is_coated=true, quantity>0) vs. aparte (is_coated=false, quantity=0)
 *  2. max_sauces incluidas por combo según el variant
 *  3. Suma de piezas bañadas ≤ wings_count del variant
 *  4. Cobro de salsas extra difiere por sucursal:
 *     - Cochabamba: se cobra 5 Bs por salsa extra SOLO en combos de ≥12 piezas
 *     - Tarija: NUNCA se cobra por salsas extra
 */
class WingSauceValidator
{
    /** Costo por cada salsa extra (solo aplica en Cochabamba, combos ≥12) */
    private const EXTRA_SAUCE_PRICE = 5.00;

    /** Cache del slug del branch para evitar queries repetidas */
    private ?string $cachedSlug = null;
    private ?int $cachedBranchId = null;

    /**
     * Valida las salsas seleccionadas para un combo de alitas.
     *
     * @param  \App\Modules\Menu\Models\ProductVariant  $variant  El variant del producto (debe tener wings_count y max_sauces)
     * @param  int    $branchId   ID de la sucursal
     * @param  array  $sauces     Array de salsas: [['sauce_id'=>int, 'quantity'=>int, 'is_coated'=>bool], ...]
     * @return float  Cargo extra total por salsas (0.0 si no aplica)
     *
     * @throws ValidationException Si alguna regla de negocio es violada
     */
    public function validate($variant, int $branchId, array $sauces): float
    {
        // Si no hay salsas, no hay nada que validar
        if (empty($sauces)) {
            return 0.0;
        }

        $wingsCount = $variant->wings_count;
        $maxSauces  = $variant->max_sauces;

        // ─── REGLA 1: Validar estructura de cada salsa ────────────────
        foreach ($sauces as $index => $sauce) {
            $position = $index + 1;

            if (empty($sauce['sauce_id'])) {
                throw ValidationException::withMessages([
                    "sauces.{$index}.sauce_id" => "La salsa #{$position} debe tener un identificador válido.",
                ]);
            }

            // is_coated=true requiere quantity > 0 (piezas bañadas)
            if (($sauce['is_coated'] ?? true) && ($sauce['quantity'] ?? 0) <= 0) {
                throw ValidationException::withMessages([
                    "sauces.{$index}.quantity" => "La salsa #{$position} marcada como bañada debe indicar al menos 1 pieza.",
                ]);
            }

            // is_coated=false → quantity debe ser 0 (salsa aparte, sin piezas bañadas)
            if (!($sauce['is_coated'] ?? true) && ($sauce['quantity'] ?? 0) != 0) {
                throw ValidationException::withMessages([
                    "sauces.{$index}.quantity" => "La salsa #{$position} servida aparte no debe indicar piezas bañadas (quantity debe ser 0).",
                ]);
            }
        }

        // ─── REGLA 3: Piezas bañadas ≤ wings_count ───────────────────
        $totalCoatedPieces = 0;
        foreach ($sauces as $sauce) {
            if ($sauce['is_coated'] ?? true) {
                $totalCoatedPieces += ($sauce['quantity'] ?? 0);
            }
        }

        if ($totalCoatedPieces > $wingsCount) {
            throw ValidationException::withMessages([
                'sauces' => "La cantidad de piezas bañadas ({$totalCoatedPieces}) supera el total de piezas del combo ({$wingsCount}).",
            ]);
        }

        // ─── REGLA 2 y 4: Conteo de salsas distintas y cobro extra ───
        // Contar salsas DISTINTAS elegidas (bañadas + aparte)
        $distinctSauceIds = collect($sauces)->pluck('sauce_id')->unique()->count();

        // ─── REGLA 4: Cobro de salsas extra según sucursal ────────────
        $branchSlug = $this->getBranchSlug($branchId);

        if ($branchSlug === 'tja') {
            // TARIJA: nunca se cobra por salsas extra
            return 0.0;
        }

        // COCHABAMBA (y cualquier otra sucursal futura — decisión defensiva)
        if ($branchSlug === 'cbba') {
            // Solo se cobra en combos de 12 o más piezas
            if ($wingsCount < 12) {
                return 0.0;
            }

            // Salsas extra = las que exceden max_sauces
            $extraSauces = max(0, $distinctSauceIds - $maxSauces);

            return $extraSauces * self::EXTRA_SAUCE_PRICE;
        }

        // Decisión defensiva: para sucursales no reconocidas, no cobrar extra
        // y registrar un warning en el log para investigación
        \Illuminate\Support\Facades\Log::warning(
            "WingSauceValidator: sucursal con slug '{$branchSlug}' no tiene regla de cobro definida. Se asume sin cargo extra.",
            ['branch_id' => $branchId]
        );

        return 0.0;
    }

    /**
     * Obtiene el slug del branch, cacheando para evitar queries repetidas.
     */
    private function getBranchSlug(int $branchId): string
    {
        if ($this->cachedBranchId === $branchId && $this->cachedSlug !== null) {
            return $this->cachedSlug;
        }

        $branch = Branch::findOrFail($branchId);
        $this->cachedBranchId = $branchId;
        $this->cachedSlug = $branch->slug;

        return $this->cachedSlug;
    }
}
