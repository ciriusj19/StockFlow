<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertNotNull($user->refresh()->last_login_at);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_disabled_users_cannot_authenticate(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Disabled]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_disabled_authenticated_users_are_logged_out_on_their_next_request(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Disabled]);

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Responsable stock');

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response
            ->assertOk()
            ->assertSeeVolt('layout.navigation')
            ->assertSee('StockFlow')
            ->assertSee('Produits');
    }

    public function test_sidebar_navigation_respects_role_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('Administrateur');
        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');
        $warehouseKeeper = User::factory()->create();
        $warehouseKeeper->assignRole('Magasinier');
        $agencyChief = User::factory()->create();
        $agencyChief->assignRole("Chef d'agence");
        $generalDirector = User::factory()->create();
        $generalDirector->assignRole('Directeur general');

        $this->actingAs($administrator);

        Volt::test('layout.navigation')
            ->assertSee('Pilotage')
            ->assertSee('Tableau de bord')
            ->assertSee('Décisionnel')
            ->assertSee('Synthèse décisionnelle')
            ->assertSee('Administration')
            ->assertSee('Utilisateurs')
            ->assertSee('Rôles');

        $this->actingAs($manager);

        Volt::test('layout.navigation')
            ->assertSee('Pilotage')
            ->assertSee('Alertes')
            ->assertSee('Prévisions')
            ->assertSee('Stock')
            ->assertSee('Produits')
            ->assertSee('Inventaires')
            ->assertSee('Référentiel')
            ->assertSee('Catégories')
            ->assertSee('Fournisseurs')
            ->assertSee('Administration')
            ->assertSee('Rôles')
            ->assertDontSee('Décisionnel')
            ->assertDontSee('Synthèse décisionnelle')
            ->assertDontSee('Rapports')
            ->assertDontSee('Utilisateurs');

        $this->actingAs($warehouseKeeper);

        Volt::test('layout.navigation')
            ->assertSee('Stock')
            ->assertSee('Produits')
            ->assertDontSee('Pilotage')
            ->assertDontSee('Tableau de bord')
            ->assertDontSee('Alertes')
            ->assertDontSee('Prévisions')
            ->assertDontSee('Rapports')
            ->assertDontSee('Décisionnel')
            ->assertDontSee('Synthèse décisionnelle')
            ->assertDontSee('Administration');

        $this->actingAs($agencyChief);

        Volt::test('layout.navigation')
            ->assertSee('Décisionnel')
            ->assertSee('Synthèse décisionnelle')
            ->assertDontSee('Alertes')
            ->assertDontSee('Prévisions')
            ->assertDontSee('Rapports')
            ->assertDontSee('Produits')
            ->assertDontSee('Inventaires')
            ->assertDontSee('Administration');

        $this->actingAs($generalDirector);

        Volt::test('layout.navigation')
            ->assertSee('Pilotage')
            ->assertSee('Rapports')
            ->assertSee('Décisionnel')
            ->assertSee('Synthèse décisionnelle')
            ->assertDontSee('Alertes')
            ->assertDontSee('Prévisions')
            ->assertDontSee('Produits')
            ->assertDontSee('Inventaires')
            ->assertDontSee('Administration');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('layout.navigation');

        $component->call('logout');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
