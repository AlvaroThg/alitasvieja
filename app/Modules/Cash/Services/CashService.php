<?php

namespace App\Modules\Cash\Services;

use App\Models\Branch;
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
     * Registra un egreso pagado desde la Caja Chica.
     * Si la Caja Chica no tiene saldo suficiente, traspasa automáticamente la
     * diferencia desde la Caja de Venta (si esta tiene fondos suficientes).
     *
     * @throws ValidationException
     */
    public function registerPettyExpense(CashSession $session, int $userId, float $amount, string $concept, ?string $reference = null): void
    {
        if (!$session->is_open) {
            throw ValidationException::withMessages(['session' => 'La sesión de caja ya está cerrada.']);
        }
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'El monto debe ser mayor a 0.']);
        }
        if (empty($concept)) {
            throw ValidationException::withMessages(['concept' => 'El concepto del egreso es obligatorio.']);
        }

        DB::transaction(function () use ($session, $userId, $amount, $concept, $reference) {
            $branch = Branch::where('id', $session->branch_id)->lockForUpdate()->first();
            $pettyBalance = (float) $branch->petty_cash_balance;

            // Si la Caja Chica no alcanza, reponer la diferencia desde la Caja de Venta.
            if ($amount > $pettyBalance) {
                $shortfall = round($amount - $pettyBalance, 2);
                $salesAvailable = $session->fresh()->calculateExpected();

                if ($salesAvailable + 0.001 < $shortfall) {
                    throw ValidationException::withMessages([
                        'amount' => 'Caja de Venta no tiene saldo suficiente para reponer la Caja Chica (faltan '
                            . number_format($shortfall, 2) . ' Bs).',
                    ]);
                }

                CashMovement::create([
                    'cash_session_id' => $session->id,
                    'user_id'         => $userId,
                    'type'            => 'expense',
                    'cash_box'        => 'transfer',
                    'amount'          => $shortfall,
                    'concept'         => 'Traspaso automático a Caja Chica',
                    'reference'       => null,
                ]);
                $pettyBalance += $shortfall;
            }

            CashMovement::create([
                'cash_session_id' => $session->id,
                'user_id'         => $userId,
                'type'            => 'expense',
                'cash_box'        => 'petty',
                'amount'          => $amount,
                'concept'         => $concept,
                'reference'       => $reference,
            ]);

            $branch->petty_cash_balance = round($pettyBalance - $amount, 2);
            $branch->save();
        });
    }

    /**
     * Carga manual de fondos a la Caja Chica, traspasados desde la Caja de Venta.
     *
     * @throws ValidationException
     */
    public function loadPettyCash(CashSession $session, int $userId, float $amount): void
    {
        if (!$session->is_open) {
            throw ValidationException::withMessages(['session' => 'La sesión de caja ya está cerrada.']);
        }
        if ($amount <= 0) {
            throw ValidationException::withMessages(['petty_amount' => 'El monto debe ser mayor a 0.']);
        }

        DB::transaction(function () use ($session, $userId, $amount) {
            $branch = Branch::where('id', $session->branch_id)->lockForUpdate()->first();
            $salesAvailable = $session->fresh()->calculateExpected();

            if ($salesAvailable + 0.001 < $amount) {
                throw ValidationException::withMessages([
                    'petty_amount' => 'Caja de Venta no tiene saldo suficiente para esta carga.',
                ]);
            }

            CashMovement::create([
                'cash_session_id' => $session->id,
                'user_id'         => $userId,
                'type'            => 'expense',
                'cash_box'        => 'transfer',
                'amount'          => $amount,
                'concept'         => 'Carga manual a Caja Chica',
                'reference'       => null,
            ]);

            $branch->petty_cash_balance = round((float) $branch->petty_cash_balance + $amount, 2);
            $branch->save();
        });
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
