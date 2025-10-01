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
            'name' => 'Mülken Burak LALE',
            'email' => 'mulkenburak@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('123456')
        ];

        User::firstOrCreate(
            ['email' => $userData['email']],
            $userData
        );

        $this->command->info("UserSeeder worked");
    }
}
