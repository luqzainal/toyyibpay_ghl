<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create test integrations with related data
        \App\Models\Integration::factory(3)
            ->has(\App\Models\ToyyibPayConfig::factory()->sandboxOnly(), 'toyyibPayConfig')
            ->has(\App\Models\Transaction::factory(5)->pending(), 'transactions')
            ->create();

        // Create one production integration with completed transactions
        \App\Models\Integration::factory()
            ->has(\App\Models\ToyyibPayConfig::factory()->production(), 'toyyibPayConfig')
            ->has(\App\Models\Transaction::factory(3)->completed()->production(), 'transactions')
            ->create();

        // Create one inactive integration
        \App\Models\Integration::factory()
            ->inactive()
            ->has(\App\Models\ToyyibPayConfig::factory()->unconfigured(), 'toyyibPayConfig')
            ->create();
    }
}
