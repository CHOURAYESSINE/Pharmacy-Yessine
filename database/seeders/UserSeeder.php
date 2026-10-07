<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('users')->insert([
        //     'name' => "Jamal Admin",
        //     'email' => "admin@admin.com",
        //     'password' => Hash::make(env('ADMIN_PASSWORD') ?: (app()->environment('production') ? throw new \RuntimeException('ADMIN_PASSWORD is required') : 'admin')),
        // ]);
       $user = User::create([
            'name' => "Jamal Admin",
            'email' => "admin@admin.com",
            'password' => Hash::make(env('ADMIN_PASSWORD') ?: (app()->environment('production') ? throw new \RuntimeException('ADMIN_PASSWORD is required') : 'admin')),
        ]);
        $user->assignRole('super-admin');
    }
}

