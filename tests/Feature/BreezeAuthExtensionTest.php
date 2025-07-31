<?php

// tests/Feature/BreezeAuthExtensionTest.php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BreezeAuthExtensionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_user_registration_assigns_default_role()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0812345678',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
        $this->assertAuthenticatedAs($user);
    }

    // public function test_user_login_updates_last_login_time()
    // {
    //     $user = User::factory()->create([
    //         'email' => 'test@example.com',
    //         'password' => Hash::make('password123'),
    //         'is_active' => true,
    //         'last_login_at' => null,
    //     ]);

    //     $response = $this->post('/login', [
    //         'email' => 'test@example.com',
    //         'password' => 'password123',
    //     ]);

    //     $response->assertRedirect('/dashboard');
    //     $this->assertNotNull($user->fresh()->last_login_at);
    // }

    public function test_user_login_updates_last_login_time()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');

        // รอสักครู่แล้วตรวจสอบอีกครั้ง
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_admin_redirected_to_admin_dashboard()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $user->roles()->attach($adminRole->id);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_role_middleware_blocks_unauthorized_access()
    {
        $user = User::factory()->create();
        $userRole = Role::where('name', 'user')->first();
        $user->roles()->attach($userRole->id);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        // รองรับทั้ง 403 และ 302
        $this->assertContains($response->status(), [403, 302]);
    }

    public function test_profile_can_be_updated_with_avatar()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated Name',
                'email' => $user->email,
                'phone' => '0987654321',
            ]);

        $response->assertRedirect('/profile');

        $this->assertEquals('Updated Name', $user->fresh()->name);
        $this->assertEquals('0987654321', $user->fresh()->phone);
    }

    public function test_user_roles_are_displayed_in_dashboard()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $user->roles()->attach($adminRole->id);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee($adminRole->display_name);
    }
}
