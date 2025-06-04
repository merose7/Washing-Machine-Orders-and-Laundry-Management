<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationRoleSelectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registration_form_displays_role_selection()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('name="role"');
        $response->assertSee('option value="customer"');
        $response->assertSee('option value="admin"');
    }

    /** @test */
    public function user_can_register_with_selected_role()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',
        ]);

        $response->assertRedirect('/laundryhome');

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function registration_fails_with_invalid_role()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'testuser2@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'invalidrole',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', [
            'email' => 'testuser2@example.com',
        ]);
    }
}
