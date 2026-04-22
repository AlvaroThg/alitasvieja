<?php

namespace App\Modules\Cash\Services;

use App\Modules\Cash\Models\CashMovement;
use App\Modules\Cash\Models\CashSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashService
{
    /**
     * Abre una nueva sesión de caja.
     *
     * @throws ValidationException
     */
    public function openSession(int $branchId, int $userId, float $openingAmount): CashSession
    {
        if ($openingAmount < 0) {
            throw ValidationException::withMessages([
                'opening_amount' => 'El monto de apertura no puede ser negativo.',
            ]);
        }

        return DB::transaction(function () use ($branchId, $userId, $openingAmount) {
            // Verificar que no exista una sesión abierta para este branch
            $existingOpen = CashSession::byBranch($branchId)
                ->open()
                ->lockForUpdate()
                ->first();

            if ($existingOpen) {
                throw ValidationException::withMessages([
                    'session' => 'Ya hay una caja abierta para esta sucursal. Cerrala antes de abrir una nueva.',
                ]);
            }

            return CashSession::create([
                'branch_id'      => $branchId,
                'opened_by'      => $userId,
                'opening_amount' => $openingAmount,
                'opened_at'      => now(),
            ]);
        });
    }

    /**
     * Agrega un movimiento (ingreso o egreso) a la sesión.
     *
     * @throws ValidationException
     */
    public function addMovement(CashSession $session, int $userId, array $data): CashMovement
    {
        if (!$session->is_open) {
            throw ValidationException::withMessages([
                'session' => 'La sesión de caja ya está cerrada.',
            ]);
        }

        if (!isset($data['amount']) || $data['amount'] <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'El monto debe ser mayor a 0.',
            ]);
        }

        if (empty($data['concept'])) {
            throw ValidationException::withMessages([
                'concept' => 'El concepto del movimiento es obligatorio.',
            ]);
        }

        return CashMovement::create([
            'cash_session_id' => $session->id,
            'user_id'         => $userId,
            'type'            => $data['type'],
            'amount'          => $data['amount'],
            'concept'         => $data['concept'],
            'reference'       => $data['reference'] ?? null,
        ]);
    }

    /**
     * Cierra una sesión de caja.
     *
     * @throws ValidationException
     */
    public function closeSession(CashSession $session, int $userId, float $closingAmount, ?string $notes = null): CashSession
    {
        if (!$session->is_open) {
            throw ValidationException::withMessages([
                'session' => 'La sesión de caja ya está cerrada.',
            ]);
        }

        if ($closingAmount < 0) {
            throw ValidationException::withMessages([
                'closing_amount' => 'El monto de cierre no puede ser negativo.',
            ]);
        }

        return DB::transaction(function () use ($session, $userId, $closingAmount, $notes) {
            $expectedAmount = $session->calculateExpected();
            $difference = $closingAmount - $expectedAmount;

            $session->update([
                'closing_amount'  => $closingAmount,
                'expected_amount' => $expectedAmount,
                'difference'      => $difference,
                'notes'           => $notes,
                'closed_by'       => $userId,
                'closed_at'       => now(),
            ]);

            return $session->fresh();
        });
    }

    /**
     * Retorna la sesión abierta del branch, o null.
     */
    public function getActiveSession(int $branchId): ?CashSession
    {
        return CashSession::byBranch($branchId)
            ->open()
            ->with(['openedBy:id,name', 'movements'])
            ->first();
    }

    /**
     * Resumen de una sesión de caja.
     */
    public function getSummary(CashSession $session): array
    {
        return [
            'opening_amount'  => (float) $session->opening_amount,
            'total_incomes'   => $session->total_incomes,
            'total_expenses'  => $session->total_expenses,
            'expected_amount' => $session->calculateExpected(),
            'movements'       => $session->movements()->with('user:id,name')->get(),
        ];
    }
}
