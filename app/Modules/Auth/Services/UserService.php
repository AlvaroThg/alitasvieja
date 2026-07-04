<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService
{
    private const VALID_ROLES = ['owner', 'branch_admin', 'cashier', 'waiter'];

    /**
     * Retorna todos los usuarios, opcionalmente filtrados por branch.
     */
    public function getAll(?int $branchId = null): Collection
    {
        $query = User::with('branch:id,name,city');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->orderBy('branch_id', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Crea un nuevo usuario.
     *
     * @throws ValidationException
     */
    public function create(array $data): User
    {
        $this->validateUserData($data, isCreate: true);

        return User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'],  // Se hashea automáticamente por el cast 'hashed'
            'role'      => $data['role'],
            'branch_id' => $data['branch_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Actualiza un usuario existente (actualización parcial).
     *
     * @throws ValidationException
     */
    public function update(User $user, array $data): User
    {
        $this->validateUserData($data, isCreate: false, existingUser: $user);

        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        if (isset($data['password'])) {
            $updateData['password'] = $data['password'];  // Cast 'hashed' lo hashea
        }

        if (isset($data['role'])) {
            $updateData['role'] = $data['role'];
        }

        if (array_key_exists('branch_id', $data)) {
            $updateData['branch_id'] = $data['branch_id'];
        }

        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }

        $user->update($updateData);

        return $user->fresh('branch:id,name,city');
    }

    /**
     * Alterna el estado activo/inactivo de un usuario.
     *
     * @throws ValidationException
     */
    public function toggleActive(User $user): User
    {
        // No se puede desactivar al propio usuario
        if (auth()->id() === $user->id) {
            throw ValidationException::withMessages([
                'user' => 'No podés desactivar tu propio usuario.',
            ]);
        }

        // Si se va a desactivar (is_active=true → false), verificar restricciones
        if ($user->is_active && $user->role === 'owner') {
            $activeOwners = User::where('role', 'owner')
                ->where('is_active', true)
                ->count();

            if ($activeOwners <= 1) {
                throw ValidationException::withMessages([
                    'user' => 'Debe existir al menos un owner activo.',
                ]);
            }
        }

        $user->update(['is_active' => !$user->is_active]);

        return $user->fresh();
    }

    /**
     * Eliminación lógica: anonimiza los datos del usuario.
     *
     * @throws ValidationException
     */
    public function delete(User $user): void
    {
        // No se puede eliminar al propio usuario
        if (auth()->id() === $user->id) {
            throw ValidationException::withMessages([
                'user' => 'No podés eliminar tu propio usuario.',
            ]);
        }

        // Restricción del último owner activo
        if ($user->role === 'owner' && $user->is_active) {
            $activeOwners = User::where('role', 'owner')
                ->where('is_active', true)
                ->count();

            if ($activeOwners <= 1) {
                throw ValidationException::withMessages([
                    'user' => 'Debe existir al menos un owner activo.',
                ]);
            }
        }

        $user->update([
            'is_active' => false,
            'email'     => "deleted_{$user->id}@alitasvega.internal",
            'name'      => 'Usuario eliminado',
            'password'  => Str::random(32),  // Cast 'hashed' lo hashea
        ]);
    }

    // ─── Validación privada ──────────────────────────────────

    /**
     * @throws ValidationException
     */
    private function validateUserData(array $data, bool $isCreate, ?User $existingUser = null): void
    {
        // Email: obligatorio en create, opcional en update
        if ($isCreate && empty($data['email'])) {
            throw ValidationException::withMessages([
                'email' => 'El email es obligatorio.',
            ]);
        }

        if (isset($data['email'])) {
            $emailQuery = User::where('email', $data['email']);
            if ($existingUser) {
                $emailQuery->where('id', '!=', $existingUser->id);
            }
            if ($emailQuery->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'El email ya está registrado por otro usuario.',
                ]);
            }
        }

        // Password: obligatorio en create, opcional en update
        if ($isCreate && empty($data['password'])) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña es obligatoria.',
            ]);
        }

        if (isset($data['password']) && strlen($data['password']) < 8) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña debe tener al menos 8 caracteres.',
            ]);
        }

        // Role
        if ($isCreate && empty($data['role'])) {
            throw ValidationException::withMessages([
                'role' => 'El rol es obligatorio.',
            ]);
        }

        if (isset($data['role'])) {
            if (!in_array($data['role'], self::VALID_ROLES)) {
                throw ValidationException::withMessages([
                    'role' => 'El rol no es válido. Use: ' . implode(', ', self::VALID_ROLES),
                ]);
            }

            // Owner no necesita branch_id; el resto sí
            $role = $data['role'];
            $branchId = $data['branch_id'] ?? ($existingUser?->branch_id);

            if ($role === 'owner' && !empty($data['branch_id'])) {
                throw ValidationException::withMessages([
                    'branch_id' => 'El owner no debe tener sucursal asignada.',
                ]);
            }

            if ($role !== 'owner') {
                // Si es create, o si se cambia el role en update, verificar branch_id
                if ($isCreate && empty($data['branch_id'])) {
                    throw ValidationException::withMessages([
                        'branch_id' => 'La sucursal es obligatoria para el rol ' . $role . '.',
                    ]);
                }
            }
        }

        // Name obligatorio en create
        if ($isCreate && empty($data['name'])) {
            throw ValidationException::withMessages([
                'name' => 'El nombre es obligatorio.',
            ]);
        }
    }
}
