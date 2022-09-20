<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginValidationWorks()
    {
        $this->postJson('/api/login')
        ->assertStatus(422)
        ->assertValidationErrors(['name','email','password']);
    }

    public function testCannotLoginWithIncorrectEmail()
    {
        $this->postJson('/api/login', [
            'email' => 'idonotexist@test.com',
            'password' => '123456',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testCannotLoginWithIncorrectPassword()
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'iamwrong',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testAUserCanLogin()
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '123456',
        ])->assertOk()
            ->assertJsonStructure(['token']);
    }
}