<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_update_disable_and_reactivate_a_user(): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Utilisateurs');

        $this->actingAs($administrator)->post(route('users.store'), [
            'name' => 'Magasinier Demo',
            'email' => 'magasinier@stockflow.local',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_name' => 'Magasinier',
        ])->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'magasinier@stockflow.local')->firstOrFail();
        $this->assertTrue($user->hasRole('Magasinier'));
        $this->assertSame(UserStatus::Active, $user->status);

        $this->actingAs($administrator)->put(route('users.update', $user), [
            'name' => 'Responsable Stock Demo',
            'email' => 'responsable-stock@stockflow.local',
            'role_name' => 'Responsable stock',
        ])->assertRedirect(route('users.index'));

        $this->assertTrue($user->refresh()->hasRole('Responsable stock'));

        $this->actingAs($administrator)
            ->patch(route('users.toggle-status', $user))
            ->assertRedirect(route('users.index'));
        $this->assertSame(UserStatus::Disabled, $user->refresh()->status);

        $this->actingAs($administrator)
            ->patch(route('users.toggle-status', $user))
            ->assertRedirect(route('users.index'));
        $this->assertSame(UserStatus::Active, $user->refresh()->status);
    }

    public function test_administrator_can_create_and_configure_a_custom_role(): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSeeText("Rôles et droits d'accès", false);

        $this->actingAs($administrator)->post(route('roles.store'), [
            'name' => 'Auditeur',
            'permissions' => ['products.view', 'reports.view'],
        ])->assertRedirect(route('roles.index'));

        $role = \Spatie\Permission\Models\Role::findByName('Auditeur');
        $this->assertTrue($role->hasPermissionTo('products.view'));
        $this->assertTrue($role->hasPermissionTo('reports.view'));

        $this->actingAs($administrator)
            ->get(route('roles.edit', $role))
            ->assertOk()
            ->assertSeeText('Consulter les produits')
            ->assertSeeText('Exporter les rapports')
            ->assertDontSeeText('products.view');

        $this->actingAs($administrator)->put(route('roles.update', $role), [
            'name' => 'Auditeur',
            'permissions' => ['products.view', 'reports.view', 'forecasts.view'],
        ])->assertRedirect(route('roles.index'));

        $this->assertTrue($role->fresh()->hasPermissionTo('forecasts.view'));
    }

    public function test_stock_manager_can_view_roles_but_cannot_manage_roles_or_users(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');

        $this->actingAs($manager)->get(route('roles.index'))->assertOk();
        $this->actingAs($manager)->get(route('roles.create'))->assertForbidden();
        $this->actingAs($manager)->get(route('users.index'))->assertForbidden();
    }

    public function test_administrator_cannot_disable_their_own_account(): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->patch(route('users.toggle-status', $administrator))
            ->assertSessionHasErrors('status');

        $this->assertSame(UserStatus::Active, $administrator->refresh()->status);
    }

    private function administrator(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole('Administrateur');

        return $administrator;
    }
}
