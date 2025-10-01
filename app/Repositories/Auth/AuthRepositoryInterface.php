<?php

namespace App\Repositories\Auth;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

interface AuthRepositoryInterface
{
    public function findToken($token): ?PersonalAccessToken;
    public function deleteTokens(User $user): void;
    public function deleteToken(User $user, string $tokenName): void;
}
