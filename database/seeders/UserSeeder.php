<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userData = [
            'name' => 'Examle user',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password')
        ];

        User::firstOrCreate(
            ['email' => $userData['email']],
            $userData
        );

        $this->command->info("UserSeeder worked");
    }
}
