<?php

namespace App\Services\Auth;

use App\Models\User;

interface AuthServiceInterface
{
    public function login(User $user): User;
    public function refreshToken(string $currentRefreshToken): User;
    public function logout(User $user): void;

}
