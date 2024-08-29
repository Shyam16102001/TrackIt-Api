<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Groups;
use App\Models\Transaction;
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
        User::factory(3)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Category::factory(5)->create();
        Groups::factory(5)->create();
        Transaction::factory(10)->create();
    }
}
