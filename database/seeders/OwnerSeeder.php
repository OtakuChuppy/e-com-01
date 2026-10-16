<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        // Credentials come from env at seed time; never hardcode or commit real passwords.
        $email = env('OWNER_EMAIL', 'owner@example.com');
        $password = env('OWNER_PASSWORD', 'ChangeMe-12345!');
        $name = env('OWNER_NAME', 'Store Owner');

        User::firstOrCreate(['email' => $email], [
            'name' => $name,
            'password' => $password, // hashed via 'hashed' cast
            'role' => 'owner',
            'is_active' => true,
        ]);
    }
}
