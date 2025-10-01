<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createUserAndLogin()
{
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password')
    ]);

    return test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password'
    ]);
}

it('user can login', function () {

    $loginResponse = createUserAndLogin();

    $loginResponse->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'access_token',
                'access_token_expires_at',
                'refresh_token',
                'refresh_token_expires_at',
                'token_type',
                'logged_in_at'
            ]
        ]);
});

it('user can refresh tokens', function () {

    $loginResponse = createUserAndLogin();

    $refreshToken = $loginResponse->json('data.refresh_token');

    $refreshResponse = $this->withHeader('Authorization', 'Bearer ' . $refreshToken)
        ->postJson('/api/refresh-token');

    $refreshResponse->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'access_token',
                'access_token_expires_at',
                'refresh_token',
                'refresh_token_expires_at',
                'token_type',
                'logged_in_at'
            ]
        ]);
});

it('user can logout', function () {

    $loginResponse = createUserAndLogin();

    $accessToken = $loginResponse->json('data.access_token');

    $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $accessToken)->get('/api/user/logout');

    $logoutResponse->assertStatus(200)
        ->assertJson([
            'message' => __('auth.logout')
        ]);
});

it('user can get logged in user', function () {

    $loginResponse = createUserAndLogin();

    $accessToken = $loginResponse->json('data.access_token');

    $loggedInUserResponse = $this->withHeader('Authorization', 'Bearer ' . $accessToken)
        ->get('/api/user/');

    $loggedInUserResponse->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email'
            ]
        ]);
});
