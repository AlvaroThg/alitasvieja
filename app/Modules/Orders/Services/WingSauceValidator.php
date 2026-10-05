<?php

namespace App\Modules\Orders\Services;

use App\Models\Branch;
use Illuminate\Validation\ValidationException;

/**
 * Valida las reglas de negocio de salsas para combos de alitas.
 *
 * REGLAS IMPLEMENTADAS:
 *  1. Salsas bañadas (is_coated=true, quantity>0) vs. aparte (is_coated=false, quantity>0)
 *  2. max_sauces incluidas por combo según el variant
 *  3. Suma de piezas bañadas + aparte ≤ wings_count del variant
 *  4. Cobro de salsas extra difiere por sucursal:
 *     - Cochabamba:
 *       a) Alitas bañadas en porciones de 12, 16 o 24 piezas: recargo automático de 5 Bs por porción.
 *       b) Salsas extra que excedan max_sauces: 5 Bs por cada salsa extra.
 *     - Tarija: NUNCA se cobra por salsas extra o bañadas.
 */
class WingSauceValidator
{
    /** Costo base por recargo de salsas extra o bañadas en Cochabamba */
    private const EXTRA_SAUCE_PRICE = 5.00;

    /** Cache del slug de la sucursal */
    private ?string $cachedSlug = null;
    private ?int $cachedBranchId = null;

    /**
     * Valida las salsas seleccionadas para un combo de alitas.
     *
     * @param  mixed  $variant  El variant del producto (debe tener wings_count y max_sauces)
     * @param  int    $branchId   ID de la sucursal
     * @param  array  $sauces     Array de salsas: [['sauce_id'=>int, 'quantity'=>int, 'is_coated'=>bool], ...]
     * @param  int    $quantity   Cantidad de porciones/combos comprados
     * @return float  Cargo extra total por salsas (0.0 si no aplica)
     *
     * @throws ValidationException Si alguna regla de negocio es violada
     */
    public function validate($variant, int $branchId, array $sauces, int $quantity = 1): float
    {
        // Si no hay salsas, no hay nada que validar
        if (empty($sauces)) {
            return 0.0;
        }

        $wingsCount = $variant->wings_count * $quantity;
        $maxSauces  = $variant->max_sauces * $quantity;

        // ─── REGLA 1: Validar estructura de cada salsa ────────────────
        foreach ($sauces as $index => $sauce) {
            $position = $index + 1;

            if (empty($sauce['sauce_id'])) {
                throw ValidationException::withMessages([
                    "sauces.{$index}.sauce_id" => "La salsa #{$position} debe tener un identificador válido.",
                ]);
            }

            if (($sauce['quantity'] ?? 0) <= 0) {
                throw ValidationException::withMessages([
                    "sauces.{$index}.quantity" => "La salsa #{$position} " . (!empty($sauce['is_coated']) ? 'bañada' : 'aparte') . " debe indicar al menos 1 pieza.",
                ]);
            }
        }

        // ─── REGLA 3: Piezas bañadas/aparte ≤ wings_count (Solo para alitas) ───
        $product = $variant->product ?? null;
        $isWings = $product ? (bool) $product->is_wings : ($variant->wings_count > 0);

        if ($isWings && $wingsCount > 0) {
            $totalAssignedPieces = 0;
            foreach ($sauces as $sauce) {
                $totalAssignedPieces += ($sauce['quantity'] ?? 0);
            }

            if ($totalAssignedPieces > $wingsCount) {
                throw ValidationException::withMessages([
                    'sauces' => "La cantidad de alitas asignadas ({$totalAssignedPieces}) supera el total de piezas del combo ({$wingsCount}).",
                ]);
            }
        }

        // ─── REGLA 2 y 4: Conteo de salsas distintas y cobro extra ───
        $distinctSauceIds = collect($sauces)->pluck('sauce_id')->unique()->count();

        // ─── REGLA 4: Cobro de salsas extra según sucursal ────────────
        $branchSlug = $this->getBranchSlug($branchId);

        if ($branchSlug === 'tja') {
            // TARIJA: nunca se cobra por salsas extra ni bañadas
            return 0.0;
        }

        if ($this->isCochabamba($branchId, $branchSlug)) {
            $charge = 0.0;

            // ¿Hay alitas / picadas bañadas?
            $hasCoated = collect($sauces)->contains(function ($s) {
                return !empty($s['is_coated']) && ($s['quantity'] ?? 0) > 0;
            });

            // Cobrar por bañadas en Cochabamba si el producto es de alitas o el dueño activó el cobro (ej. Picadas)
            $product = $variant->product ?? null;
            $shouldChargeCoated = true;
            if ($product) {
                $shouldChargeCoated = (bool) ($product->is_wings || $product->charge_coated_sauces);
            }

            if ($hasCoated && $shouldChargeCoated) {
                $wingsPerPortion = (int) ($variant->wings_count ?? 0);

                if ($wingsPerPortion > 0) {
                    $totalCoatedPieces = collect($sauces)
                        ->filter(fn($s) => !empty($s['is_coated']))
                        ->sum('quantity');

                    $coatedPortions = (int) ceil($totalCoatedPieces / $wingsPerPortion);
                    $coatedPortions = max(1, min($coatedPortions, $quantity));
                } else {
                    $totalCoatedCount = collect($sauces)
                        ->filter(fn($s) => !empty($s['is_coated']))
                        ->count();
                    $coatedPortions = max(1, min($totalCoatedCount, $quantity));
                }

                $charge += self::EXTRA_SAUCE_PRICE * $coatedPortions;
            }

            // Salsas extra que excedan max_sauces (5 Bs por cada salsa extra)
            $extraSaucesCount = max(0, $distinctSauceIds - $maxSauces);
            if ($extraSaucesCount > 0) {
                $charge += $extraSaucesCount * self::EXTRA_SAUCE_PRICE;
            }

            return (float) $charge;
        }

        \Illuminate\Support\Facades\Log::warning(
            "WingSauceValidator: sucursal con slug '{$branchSlug}' no tiene regla de cobro definida. Se asume sin cargo extra.",
            ['branch_id' => $branchId]
        );

        return 0.0;
    }

    private function isCochabamba(int $branchId, string $slug): bool
    {
        if ($slug === 'cbba' || str_contains($slug, 'cbba')) {
            return true;
        }

        $branch = Branch::find($branchId);
        if (!$branch) return false;

        $city = strtolower($branch->city ?? '');
        $name = strtolower($branch->name ?? '');

        return str_contains($city, 'cochabamba') || str_contains($name, 'cochabamba');
    }

    private function getBranchSlug(int $branchId): string
    {
        if ($this->cachedBranchId === $branchId && $this->cachedSlug !== null) {
            return $this->cachedSlug;
        }

        $branch = Branch::find($branchId);
        $this->cachedBranchId = $branchId;
        $this->cachedSlug = strtolower($branch->slug ?? '');

        return $this->cachedSlug;
    }
}
