<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@localhost'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'), // Ganti setelah testing
                'role' => 'admin',
            ]
        );
    }
}
