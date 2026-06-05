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
        User::create([
            'name' => 'Diana Ochoa',
            'email' => 'diochoa@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Brisa Luna',
            'email' => 'bluna@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'usuario'
        ]);
    }
}
