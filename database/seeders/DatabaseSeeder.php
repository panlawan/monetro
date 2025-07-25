<?php
// database/seeders/DatabaseSeeder.php (อัพเดท)

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);
        
        // Create test users if in development
        if (app()->environment(['local', 'development'])) {
            \App\Models\User::factory(10)->create();
        }
    }
}