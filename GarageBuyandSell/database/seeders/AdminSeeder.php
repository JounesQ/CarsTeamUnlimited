<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()
            ->where('username', 'admin')
            ->orWhere('email', 'admin@admin.com')
            ->delete();

        User::updateOrCreate(
            ['username' => 'ctuadmin@ctu.com'],
            [
                'name' => 'CTU Admin',
                'email' => 'ctuadmin@ctu.com',
                'username' => 'ctuadmin@ctu.com',
                'password' => 'ctufinancing140',
                'is_admin' => true,
            ]
        );
    }
}
