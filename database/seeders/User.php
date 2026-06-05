<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class User extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin'
        ]);

        // Usuario normal
        User::create([
            'name' => 'Usuario',
            'email' => 'usuario@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'usuario'
        ]);
    }
}
