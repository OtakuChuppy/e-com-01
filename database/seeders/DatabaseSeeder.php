<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Both seeders are idempotent (updateOrCreate/firstOrCreate) and safe to run
        // on every deploy: settings/categories refresh, owner account only created once.
        $this->call([
            DemoSeeder::class,
            OwnerSeeder::class,
        ]);
    }
}
