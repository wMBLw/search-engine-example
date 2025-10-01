<?php

namespace App\Repositories\Auth;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class AuthRepository implements AuthRepositoryInterface
{
    public function findToken($token): ?PersonalAccessToken
    {
        return PersonalAccessToken::findToken($token);
    }

    public function deleteTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function deleteToken(User $user, string $tokenName): void
    {
        $user->tokens()->where('name', $tokenName)->delete();
    }

}
