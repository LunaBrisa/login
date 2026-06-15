<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed users.
     */
    public function run(): void
    {
        // ADMIN
        User::updateOrCreate(
            [
                'email' => 'admin@test.com'
            ],
            [
                'name' => 'Diana Ochoa',

                'password' => Hash::make(
                    'AxdMccwj9ZFShw8c2pY$bP@'
                ),

                'rol' => 'admin'
            ]
        );

        // USER
        User::updateOrCreate(
            [
                'email' => 'user@test.com'
            ],
            [
                'name' => 'Usuario',

                'password' => Hash::make(
                    'VxdMscwj8ZFZhwYc2aY$bP'
                ),

                'rol' => 'user'
            ]
        );
    }
}