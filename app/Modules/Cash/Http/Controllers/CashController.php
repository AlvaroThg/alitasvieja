<?php

namespace App\Modules\Cash\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cash\Models\CashSession;
use App\Modules\Cash\Services\CashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CashController extends Controller
{
    protected CashService $cashService;

    public function __construct(CashService $cashService)
    {
        $this->cashService = $cashService;
    }

    /**
     * POST /cash/sessions/open
     */
    public function open(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $branchId = $request->user()->activeBranchId();

        if (!$branchId) {
            return response()->json(['error' => 'No hay sucursal activa.'], 422);
        }

        try {
            $session = $this->cashService->openSession(
                $branchId,
                $request->user()->id,
                (float) $validated['opening_amount']
            );

            return response()->json([
                'session_id'     => $session->id,
                'opened_at'      => $session->opened_at,
                'opening_amount' => $session->opening_amount,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * POST /cash/sessions/{session}/movements
     */
    public function addMovement(Request $request, CashSession $session): JsonResponse
    {
        $validated = $request->validate([
            'type'      => 'required|in:income,expense',
            'amount'    => 'required|numeric|min:0.01',
            'concept'   => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
        ]);

        try {
            $movement = $this->cashService->addMovement(
                $session,
                $request->user()->id,
                $validated
            );

            $summary = [
                'total_incomes'   => $session->total_incomes,
                'total_expenses'  => $session->total_expenses,
                'expected_amount' => $session->calculateExpected(),
            ];

            return response()->json([
                'movement_id'     => $movement->id,
                'type'            => $movement->type,
                'amount'          => $movement->amount,
                'concept'         => $movement->concept,
                'session_summary' => $summary,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * POST /cash/sessions/{session}/close
     */
    public function close(Request $request, CashSession $session): JsonResponse
    {
        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        try {
            $session = $this->cashService->closeSession(
                $session,
                $request->user()->id,
                (float) $validated['closing_amount'],
                $validated['notes'] ?? null
            );

            // Determinar etiqueta de diferencia
            $diff = (float) $session->difference;
            if ($diff > 0.01) {
                $diffLabel = 'SOBRANTE';
            } elseif ($diff < -0.01) {
                $diffLabel = 'FALTANTE';
            } else {
                $diffLabel = 'EXACTO';
            }

            return response()->json([
                'opening_amount'   => $session->opening_amount,
                'closing_amount'   => $session->closing_amount,
                'expected_amount'  => $session->expected_amount,
                'difference'       => $session->difference,
                'difference_label' => $diffLabel,
                'total_incomes'    => $session->total_incomes,
                'total_expenses'   => $session->total_expenses,
                'closed_at'        => $session->closed_at,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * GET /cash/sessions/{session}
     */
    public function show(CashSession $session): JsonResponse
    {
        $summary = $this->cashService->getSummary($session);

        return response()->json(array_merge(
            $session->toArray(),
            ['summary' => $summary]
        ));
    }

    /**
     * GET /cash/sessions/active
     */
    public function active(Request $request): JsonResponse
    {
        $branchId = $request->user()->activeBranchId();

        if (!$branchId) {
            return response()->json(['session' => null]);
        }

        $session = $this->cashService->getActiveSession($branchId);

        if (!$session) {
            return response()->json(['session' => null]);
        }

        $summary = $this->cashService->getSummary($session);

        return response()->json([
            'session' => array_merge($session->toArray(), ['summary' => $summary]),
        ]);
    }
}
