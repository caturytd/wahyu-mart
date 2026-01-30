<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('users')->insert([
            'username' => 'superadmin',
            'nama' => 'superadmin',
            'role' => 'superadmin',
            'password' => Hash::make('superadmin')
        ]);

          DB::table('users')->insert([
            'username' => 'admin',
            'nama' => 'admin',
            'role' => 'admin',
            'password' => Hash::make('admin')
        ]);
    }
}
