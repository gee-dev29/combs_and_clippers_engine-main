<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SubRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks to avoid constraint errors while truncating
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();
        SubRole::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define initial roles and subroles
        $rolesData = [
            [
                'name' => 'superAdmin',
                'display_name' => 'Super Admin',
                'subroles' => []
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'subroles' => [
                    ['name' => 'view_admins', 'display_name' => 'View Admins'],
                    ['name' => 'create_admins', 'display_name' => 'Create Admins'],
                    ['name' => 'edit_admins', 'display_name' => 'Edit Admins'],
                    ['name' => 'delete_admins', 'display_name' => 'Deletes Admins']
                ]
            ],
            [
                'name' => 'role',
                'display_name' => 'Role',
                'subroles' => [
                    ['name' => 'view_roles', 'display_name' => 'View Roles'],
                    ['name' => 'create_roles', 'display_name' => 'Create Roles'],
                    ['name' => 'edit_roles', 'display_name' => 'Edit Roles'],
                    ['name' => 'delete_roles', 'display_name' => 'Deletes Roles']
                ]
            ],
            [
                'name' => 'appointments',
                'display_name' => 'Appointments',
                'subroles' => [
                    ['name' => 'view_appointments', 'display_name' => 'View Appoinments'],
                    ['name' => 'delete_appointments', 'display_name' => 'Deletes Appoinments'],
                    ['name' => 'create_appointments', 'display_name' => 'Create Appoinments'],
                ]
            ],
            [
                'name' => 'accounts',
                'display_name' => 'Accounts',
                'subroles' => [
                    ['name' => 'view_accounts', 'display_name' => 'View Accounts'],
                    ['name' => 'create_accounts', 'display_name' => 'Create Accounts'],
                    ['name' => 'delete_accounts', 'display_name' => 'Deletes Accounts'],
                    ['name' => 'edit_accounts', 'display_name' => 'Edit Accounts'],
                ]
            ],
            [
                'name' => 'payments',
                'display_name' => 'Payments',
                'subroles' => [
                    ['name' => 'view_appointment_payment', 'display_name' => 'Appointment Payment'],
                    ['name' => 'view_boothrent_payment', 'display_name' => 'Booth Rent Payment'],
                    ['name' => 'view_withdrawal_payment', 'display_name' => 'Withdrawal Payment'],
                    ['name' => 'send_boothrent_reminder', 'display_name' => 'Send Booth Rent Reminder'],
                    ['name' => 'mark_aspaid', 'display_name' => 'Mark booth Rent Payment as Paid']
                ]
            ],
            [
                'name' => 'reports',
                'display_name' => 'Reports',
                'subroles' => [
                    ['name' => 'view_reports', 'display_name' => 'Generate reports'],
                    ['name' => 'download_reports', 'display_name' => 'Download Generated Report']
                ]
            ],
            [
                'name' => 'wallet',
                'display_name' => 'Wallet',
                'subroles' => []
            ],
            [
                'name' => 'stores',
                'display_name' => 'Stores',
                'subroles' => [
                    ['name' => 'view_stores', 'display_name' => 'Stores'],
                    ['name' => 'view_service_types', 'display_name' => 'Service Types'],
                    ['name' => 'delete_store', 'display_name' => 'Deletes Store'],
                    ['name' => 'create_service_types', 'display_name' => 'Create Service Type'],
                    ['name' => 'delete_service_types', 'display_name' => 'Deletes Service Type'],
                    ['name' => 'edit_service_types', 'display_name' => 'Edit Service Type'],
                ]
            ],
            [
                'name' => 'blogs',
                'display_name' => 'Blogs',
                'subroles' => []
            ]
        ];

        foreach ($rolesData as $roleData) {
            $role = Role::create([
                'name' => $roleData['name'],
                'display_name' => $roleData['display_name'],
                'description' => 'Access to ' . $roleData['display_name']
            ]);

            foreach ($roleData['subroles'] as $subroleData) {
                SubRole::create([
                    'role_id' => $role->id,
                    'name' => $subroleData['name'],
                    'display_name' => $subroleData['display_name'],
                    'description' => 'Access to ' . $subroleData['display_name']
                ]);
            }
        }
    }
}