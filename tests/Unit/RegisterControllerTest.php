<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data' => ['token', 'name'],
                 'message'
             ])
             ->assertJson(['success' => true, 'message' => 'User registered successfully.']);
    }

    public function test_user_registration_validation_fails()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);
        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'data' => ['name', 'email', 'password']
                 ])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation Error.'
                 ]);

    }

    public function test_user_registration_with_existing_email()
    {
        User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Validation Error.',
                    'data' => [
                        'email' => ['The email has already been taken.']
                    ]
                ]);
    }
    public function test_user_can_login_successfully()
    {
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['token', 'name'],
                     'message'
                 ]);
    }

    public function test_user_login_validation_fails()
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation Error.',
                     'data' => [
                         'email' => [
                             'The email field is required.'
                         ],
                         'password' => [
                             'The password field is required.'
                         ],
                     ],
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'email',
                         'password',
                     ],
                 ]);
    }




    public function test_user_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'notexist@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Login Error.',
                     'data' => [
                         'error' => 'User with this email does not exist.'
                     ],
                 ]);
    }


    public function test_user_can_logout_successfully()
    {
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'janes@example.com',
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('MyApp')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/logout');
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Successfully logged out']);
    }



    public function test_user_can_reset_password_successfully()
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->postJson('/api/password/reset', [
            'email' => 'jane@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Password reset successfully.']);
    }

    public function test_reset_password_validation_fails()
    {
        $response = $this->postJson('/api/password/reset', [
            'email' => '',
            'password' => 'short',
            'password_confirmation' => 'notmatching',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => [
                     'email',
                     'password',
                 ]]);
    }

    public function test_reset_password_user_not_found()
    {
        $response = $this->postJson('/api/password/reset', [
            'email' => 'nonexistent@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'No user found with this email.']);
    }

}
