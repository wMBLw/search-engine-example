<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this),
            'access_token' => $this->access_token,
            'access_token_expires_at' => $this->access_token_expires_at,
            'refresh_token' => $this->refresh_token,
            'refresh_token_expires_at' => $this->refresh_token_expires_at,
            'token_type' => 'Bearer',
            'logged_in_at' => Carbon::now()->toDateTimeString()
        ];

    }
}
