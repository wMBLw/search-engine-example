<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;

use App\Http\Resources\UserLoginResource;
use App\Http\Resources\UserResource;
use App\Services\AuthServiceInterface;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function login(UserLoginRequest $request): JsonResponse|JsonResource
    {
        if (!Auth::attempt($request->only('email', 'password')))
        {
            return response()->json([
                'message' => __('auth.invalid_credentials')
            ], 401);
        }

        $authUser = Auth::user();

        $loggedInUser = $this->authService->login($authUser);

        return new UserLoginResource($loggedInUser,200);
    }

    public function refreshToken(Request $request): JsonResponse|JsonResource
    {
        $currentRefreshToken = $request->bearerToken();

        $user = $this->authService->refreshToken($currentRefreshToken);

        return new UserLoginResource($user,200);
    }

    public function loggedInUser(Request $request): JsonResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function logout(): JsonResponse
    {
        $authUser = Auth::user();
        $this->authService->logout($authUser);

        return response()->json([
            'message' => __('auth.logout')
        ],200);
    }
}
