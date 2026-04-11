<?php

namespace App\Modules\Orders\Services;

use App\Models\Branch;
use Illuminate\Validation\ValidationException;

/**
 * Validador stateless de salsas para combos de alitas.
 *
 * Implementa las reglas de negocio diferenciadas por sucursal para
 * el cálculo de salsas incluidas vs. salsas extra con cargo.
 */
class WingSauceValidator
{
    /** Costo por cada salsa extra (aplica solo en Cochabamba, combos >= 12) */
    private const EXTRA_SAUCE_PRICE = 5.0;

    /** Cache del slug del branch para no repetir la query */
    private ?string $cachedSlug = null;
    private ?int $cachedBranchId = null;

    /**
     * Valida las salsas seleccionadas para un combo de alitas y retorna
     * el cargo extra total por salsas adicionales.
     *
     * @param  \App\Modules\Menu\Models\ProductVariant  $variant  (usa duck typing para evitar import circular)
     * @param  int    $branchId
     * @param  array  $sauces  [['sauce_id'=>int, 'quantity'=>int, 'is_coated'=>bool], ...]
     * @return float  Cargo extra total por salsas (0.0 si no aplica)
     *
     * @throws ValidationException  Si viola alguna regla de negocio
     */
    public function validate($variant, int $branchId, array $sauces): float
    {
        $wingsCount = $variant->wings_count;
        $maxSauces  = $variant->max_sauces;

        // ─── REGLA 1: Validar estructura de cada entrada de salsa ─────
        foreach ($sauces as $index => $sauce) {
            if ($sauce['is_coated'] && ($sauce['quantity'] ?? 0) <= 0) {
                throw ValidationException::withMessages([
                    "sauces.{$index}.quantity" => "La salsa bañada debe tener al menos 1 pieza asignada.",
                ]);
            }
        }

        // ─── REGLA 3: Restricción de piezas bañadas ──────────────────
        // La suma de quantity donde is_coated=true no puede superar wings_count
        $totalCoatedPieces = 0;
        foreach ($sauces as $sauce) {
            if ($sauce['is_coated']) {
                $totalCoatedPieces += $sauce['quantity'];
            }
        }

        if ($totalCoatedPieces > $wingsCount) {
            throw ValidationException::withMessages([
                'sauces' => "La cantidad de piezas bañadas ({$totalCoatedPieces}) supera las piezas del combo ({$wingsCount}).",
            ]);
        }

        // ─── REGLA 2 & 4: Cálculo de salsas extra y cargo ────────────
        // Contar salsas DISTINTAS elegidas (tanto bañadas como aparte)
        $distinctSauceIds = collect($sauces)->pluck('sauce_id')->unique()->count();

        $slug = $this->getBranchSlug($branchId);

        return $this->calculateExtraCharge($slug, $wingsCount, $maxSauces, $distinctSauceIds);
    }

    /**
     * Calcula el cargo extra según las reglas diferenciadas por sucursal.
     *
     * COCHABAMBA (cbba):
     *   - Solo cobra extra en combos de 12+ piezas.
     *   - Cada salsa distinta que exceda max_sauces cuesta 5 Bs.
     *   - Combos de 4, 6 u 8 piezas: NO se cobra extra nunca.
     *
     * TARIJA (tja):
     *   - NUNCA se cobra por salsas extra.
     */
    private function calculateExtraCharge(
        string $slug,
        int $wingsCount,
        int $maxSauces,
        int $distinctSauceCount
    ): float {
        // Tarija: nunca cobra extra
        if ($slug === 'tja') {
            return 0.0;
        }

        // Cochabamba: solo cobra extra en combos de 12 o más piezas
        if ($slug === 'cbba') {
            if ($wingsCount < 12) {
                return 0.0;
            }

            $extraSauces = max(0, $distinctSauceCount - $maxSauces);

            return $extraSauces * self::EXTRA_SAUCE_PRICE;
        }

        // Decisión defensiva: si el slug no coincide con ninguna
        // sucursal conocida, no cobrar extra y registrar el caso.
        // Esto no debería ocurrir en producción.
        return 0.0;
    }

    /**
     * Obtiene el slug del branch cacheándolo para evitar queries repetidas.
     */
    private function getBranchSlug(int $branchId): string
    {
        if ($this->cachedBranchId === $branchId && $this->cachedSlug !== null) {
            return $this->cachedSlug;
        }

        $branch = Branch::findOrFail($branchId);

        $this->cachedBranchId = $branchId;
        $this->cachedSlug     = $branch->slug;

        return $this->cachedSlug;
    }
}
