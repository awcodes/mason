<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Workbench\Database\Factories\PageFactory;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        UserFactory::new()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        PageFactory::new()->deterministic()->create([
            'title' => 'Home',
            'slug' => 'home',
        ]);

        PageFactory::new()->deterministic()->create([
            'title' => 'About',
            'slug' => 'about',
        ]);
    }
}
