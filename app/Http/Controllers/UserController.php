<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        abort_unless($status === null || UserStatus::tryFrom($status), 404);

        $users = User::query()
            ->with('roles')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'search', 'status'));
    }

    public function create(): View
    {
        return view('users.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $role = $validated['role_name'];
        unset($validated['role_name']);

        $user = User::query()->create($validated + [
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);
        $user->syncRoles([$role]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Utilisateur cree et role attribue.');
    }

    public function edit(User $user): View
    {
        $user->load('roles');

        return view('users.edit', ['user' => $user] + $this->formOptions());
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validated($request, $user);
        $role = $validated['role_name'];
        unset($validated['role_name']);

        if (! $request->filled('password')) {
            unset($validated['password']);
        }

        $this->ensureAdministratorRemains($user, $role, $user->status);

        $user->update($validated);
        $user->syncRoles([$role]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Utilisateur mis a jour.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages([
                'status' => 'Vous ne pouvez pas désactiver votre propre compte.',
            ]);
        }

        $status = $user->status === UserStatus::Active
            ? UserStatus::Disabled
            : UserStatus::Active;

        $this->ensureAdministratorRemains($user, $user->roles->first()?->name, $status);
        $user->update(['status' => $status]);

        return redirect()
            ->route('users.index')
            ->with('success', $status === UserStatus::Active ? 'Utilisateur réactivé.' : 'Utilisateur désactivé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'roles' => Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user),
            ],
            'password' => [
                $user ? 'nullable' : 'required',
                'string',
                'confirmed',
                Rules\Password::defaults(),
            ],
            'role_name' => [
                'required',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
        ]);
    }

    private function ensureAdministratorRemains(User $user, ?string $role, UserStatus $status): void
    {
        if (
            $user->hasRole('Administrateur')
            && ($role !== 'Administrateur' || $status !== UserStatus::Active)
            && User::role('Administrateur')->where('status', UserStatus::Active->value)->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'role_name' => 'Au moins un administrateur actif doit rester disponible.',
            ]);
        }
    }
}
