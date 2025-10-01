<?php

use App\Models\User;
use App\Services\AuthService;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Exceptions\RefreshTokenExpiredException;
use Illuminate\Support\Facades\Config;
use Mockery;

beforeEach(function () {

    $this->authRepo = Mockery::mock(AuthRepositoryInterface::class);

    $this->authService = new AuthService($this->authRepo);

    $this->user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com'
    ]);

});

it('creates tokens', function () {

    $this->authRepo->shouldReceive('deleteToken')->twice();

    $user = $this->authService->login($this->user);

    expect($user->access_token)->not->toBeNull();
    expect($user->refresh_token)->not->toBeNull();

});

it('throws exception if refresh token expired', function () {

    $this->authRepo->shouldReceive('findToken')
        ->once()
        ->with('fake-token')
        ->andReturn(null);

    $this->authService->refreshToken('fake-token');

})->throws(RefreshTokenExpiredException::class);
