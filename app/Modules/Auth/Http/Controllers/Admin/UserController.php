<?php

namespace App\Modules\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * GET /admin/users
     * Listado con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ? (int) $request->input('branch_id') : null;
        $users = $this->userService->getAll($branchId);

        // Filtros adicionales opcionales
        if ($request->filled('role')) {
            $users = $users->where('role', $request->input('role'));
        }

        if ($request->has('is_active')) {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $users = $users->where('is_active', $isActive);
            }
        }

        return response()->json(['data' => $users->values()]);
    }

    /**
     * POST /admin/users
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'password'  => 'required|string|min:8',
            'role'      => 'required|in:owner,branch_admin,cashier,waiter',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $user = $this->userService->create($validated);

            return response()->json([
                'user_id'   => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'branch_id' => $user->branch_id,
                'is_active' => $user->is_active,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * PUT /admin/users/{user}
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:255',
            'password'  => 'nullable|string|min:8',
            'role'      => 'nullable|in:owner,branch_admin,cashier,waiter',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $user = $this->userService->update($user, $validated);

            return response()->json($user);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * PATCH /admin/users/{user}/toggle-active
     */
    public function toggleActive(User $user): JsonResponse
    {
        try {
            $user = $this->userService->toggleActive($user);

            return response()->json([
                'user_id'   => $user->id,
                'is_active' => $user->is_active,
                'message'   => $user->is_active ? 'Usuario activado.' : 'Usuario desactivado.',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * DELETE /admin/users/{user}
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->delete($user);

            return response()->json([
                'message' => 'Usuario eliminado correctamente.',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
