<?php

namespace App\Services;

use App\Exceptions\RefreshTokenExpiredException;
use App\Models\User;
use App\Repositories\Auth\AuthRepositoryInterface;
use Illuminate\Support\Carbon;

class AuthService implements AuthServiceInterface
{
    public function __construct(private AuthRepositoryInterface $authRepository)
    {

    }

    private function createAccessToken(User $user): User
    {
        $this->authRepository->deleteToken($user, 'access_token');

        $user->access_token_expires_at = Carbon::now()->addMinutes(intval(config('sanctum.access_token_expiry')));

        $user->access_token = $user->createToken('access_token', ['*'], $user->access_token_expires_at)->plainTextToken;

        return $user;
    }

    private function createRefreshToken(User $user): User
    {
        $this->authRepository->deleteToken($user, 'refresh_token');

        $user->refresh_token_expires_at = Carbon::now()->addMinutes(intval(config('sanctum.refresh_token_expiry')));

        $user->refresh_token = $user->createToken('refresh_token', ['refresh'], $user->refresh_token_expires_at)->plainTextToken;

        return $user;
    }
    public function login(User $user): User
    {
        $user = $this->createAccessToken($user);
        $user = $this->createRefreshToken($user);

        return $user;
    }

    public function refreshToken(string $currentRefreshToken): User
    {
        $refreshToken = $this->authRepository->findToken($currentRefreshToken);

        if (!$refreshToken || !$refreshToken->can('refresh') || $refreshToken->expires_at->isPast()) {
            throw new RefreshTokenExpiredException();
        }

        $user = $refreshToken->tokenable; //Polymorphic relation to get the user

        $user = $this->createAccessToken($user);
        $user = $this->createRefreshToken($user);

        return $user;
    }

    public function logout(User $user): void
    {
        $this->authRepository->deleteTokens($user);
    }

}
