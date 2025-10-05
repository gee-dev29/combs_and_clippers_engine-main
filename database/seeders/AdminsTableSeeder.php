<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the admins table
        DB::table('admins')->truncate();

        // Call the RolesTableSeeder to ensure roles exist
        $this->call(RolesTableSeeder::class);

        // Create Super Admin
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('12345678'),
            'accounttype' => 'Admin',
            'role' => Crypt::encryptString(json_encode([
                'superAdmin' => true,
            ])),
            'updated_at' => now()
        ]);
    }
}