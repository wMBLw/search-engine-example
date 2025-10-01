<?php

use App\Models\User;
use App\Repositories\Auth\AuthRepository;

beforeEach(function () {
    $this->repository = new AuthRepository();
});

it('findToken returns token if exists', function () {

    $user = User::factory()->create();

    $token = $user->createToken('refresh_token', ['refresh'])->plainTextToken;

    $foundToken = $this->repository->findToken($token);

    expect($foundToken)->not->toBeNull();
    expect($foundToken->tokenable_id)->toBe($user->id);

});

it('findToken returns null if token does not exist', function () {

    $foundToken = $this->repository->findToken('non-existing-token');
    expect($foundToken)->toBeNull();

});

it('deleteToken removes specific token by name', function () {

    $user = User::factory()->create();
    $user->createToken('access_token')->plainTextToken;
    $user->createToken('refresh_token')->plainTextToken;

    $this->repository->deleteToken($user, 'refresh_token');

    $tokens = $user->tokens()->pluck('name')->toArray();

    expect($tokens)->not->toContain('refresh_token');
    expect($tokens)->toContain('access_token');

});

it('deleteTokens removes all tokens for user', function () {

    $user = User::factory()->create();
    $user->createToken('access_token')->plainTextToken;
    $user->createToken('refresh_token')->plainTextToken;

    $this->repository->deleteTokens($user);

    $tokensCount = $user->tokens()->count();

    expect($tokensCount)->toBe(0);

});
