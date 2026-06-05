<?php

namespace App\Http\Controllers;

use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('roles.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $role = Role::query()->create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role cree.');
    }

    public function edit(Role $role): View
    {
        abort_unless($role->guard_name === 'web', 404);
        $role->load('permissions');

        return view('roles.edit', ['role' => $role] + $this->formOptions());
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);
        $validated = $this->validated($request, $role);
        $permissions = $validated['permissions'] ?? [];

        if (
            in_array($role->name, ['Administrateur', 'Responsable stock', 'Gestionnaire', 'Magasinier', "Chef d'agence", 'Directeur general'], true)
            && $validated['name'] !== $role->name
        ) {
            throw ValidationException::withMessages([
                'name' => 'Les roles de base ne peuvent pas etre renommes.',
            ]);
        }

        if ($role->name === 'Administrateur') {
            $missingPermissions = Permission::query()
                ->where('guard_name', 'web')
                ->whereNotIn('name', $permissions)
                ->exists();

            if ($missingPermissions) {
                throw ValidationException::withMessages([
                    'permissions' => 'Le role Administrateur doit conserver toutes les permissions.',
                ]);
            }
        }

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($permissions);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role mis a jour.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        return [
            'permissionGroups' => PermissionCatalog::group($permissions),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where('guard_name', 'web'),
            ],
        ]);
    }
}
