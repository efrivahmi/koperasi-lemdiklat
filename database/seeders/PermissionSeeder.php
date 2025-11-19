<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define all permissions
        $permissions = [
            // Dashboard
            ['name' => 'view-dashboard', 'description' => 'View dashboard'],

            // Users Management
            ['name' => 'view-users', 'description' => 'View users list'],
            ['name' => 'create-users', 'description' => 'Create new users'],
            ['name' => 'edit-users', 'description' => 'Edit users'],
            ['name' => 'delete-users', 'description' => 'Delete users'],
            ['name' => 'manage-roles', 'description' => 'Manage user roles'],

            // Products Management
            ['name' => 'view-products', 'description' => 'View products'],
            ['name' => 'create-products', 'description' => 'Create products'],
            ['name' => 'edit-products', 'description' => 'Edit products'],
            ['name' => 'delete-products', 'description' => 'Delete products'],

            // Categories Management
            ['name' => 'view-categories', 'description' => 'View categories'],
            ['name' => 'create-categories', 'description' => 'Create categories'],
            ['name' => 'edit-categories', 'description' => 'Edit categories'],
            ['name' => 'delete-categories', 'description' => 'Delete categories'],

            // Stock Management
            ['name' => 'view-stock', 'description' => 'View stock'],
            ['name' => 'manage-stock-in', 'description' => 'Manage stock in'],
            ['name' => 'view-stock-history', 'description' => 'View stock history'],

            // Students Management
            ['name' => 'view-students', 'description' => 'View students'],
            ['name' => 'create-students', 'description' => 'Create students'],
            ['name' => 'edit-students', 'description' => 'Edit students'],
            ['name' => 'delete-students', 'description' => 'Delete students'],
            ['name' => 'generate-student-card', 'description' => 'Generate student cards'],

            // POS/Kasir
            ['name' => 'access-pos', 'description' => 'Access POS system'],
            ['name' => 'process-sales', 'description' => 'Process sales transactions'],
            ['name' => 'print-receipt', 'description' => 'Print receipts'],

            // Transactions
            ['name' => 'view-transactions', 'description' => 'View all transactions'],
            ['name' => 'topup-balance', 'description' => 'Topup student balance'],
            ['name' => 'view-own-transactions', 'description' => 'View own transactions only'],

            // Vouchers
            ['name' => 'view-vouchers', 'description' => 'View vouchers'],
            ['name' => 'create-vouchers', 'description' => 'Create/generate vouchers'],
            ['name' => 'redeem-vouchers', 'description' => 'Redeem vouchers'],

            // Expenses
            ['name' => 'view-expenses', 'description' => 'View expenses'],
            ['name' => 'create-expenses', 'description' => 'Create expenses'],
            ['name' => 'edit-expenses', 'description' => 'Edit expenses'],
            ['name' => 'delete-expenses', 'description' => 'Delete expenses'],

            // Reports
            ['name' => 'view-financial-reports', 'description' => 'View financial reports'],
            ['name' => 'view-sales-reports', 'description' => 'View sales reports'],
            ['name' => 'view-inventory-reports', 'description' => 'View inventory reports'],
            ['name' => 'view-student-transaction-reports', 'description' => 'View student transaction reports'],
            ['name' => 'export-reports', 'description' => 'Export reports'],

            // System Settings
            ['name' => 'manage-settings', 'description' => 'Manage system settings'],
            ['name' => 'view-audit-logs', 'description' => 'View audit logs'],
        ];

        // Insert permissions
        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Get permission IDs
        $permissionMap = DB::table('permissions')->pluck('id', 'name')->toArray();

        // Define role permissions
        $rolePermissions = [
            // Master - Full access
            'master' => array_keys($permissionMap),

            // Admin - Manage everything except system settings and roles
            'admin' => [
                'view-dashboard',
                'view-products', 'create-products', 'edit-products', 'delete-products',
                'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
                'view-stock', 'manage-stock-in', 'view-stock-history',
                'view-students', 'create-students', 'edit-students', 'delete-students', 'generate-student-card',
                'access-pos', 'process-sales', 'print-receipt',
                'view-transactions', 'topup-balance',
                'view-vouchers', 'create-vouchers', 'redeem-vouchers',
                'view-expenses', 'create-expenses', 'edit-expenses', 'delete-expenses',
                'view-financial-reports', 'view-sales-reports', 'view-inventory-reports', 'view-student-transaction-reports', 'export-reports',
            ],

            // Kasir - POS, basic transactions, and limited views
            'kasir' => [
                'view-dashboard',
                'view-products',
                'view-stock',
                'view-students',
                'access-pos', 'process-sales', 'print-receipt',
                'view-transactions', 'topup-balance',
                'redeem-vouchers',
            ],

            // Siswa - Only view own data
            'siswa' => [
                'view-dashboard',
                'view-own-transactions',
            ],
        ];

        // Insert role permissions
        foreach ($rolePermissions as $role => $permissions) {
            foreach ($permissions as $permissionName) {
                if (isset($permissionMap[$permissionName])) {
                    DB::table('role_permissions')->insert([
                        'role' => $role,
                        'permission_id' => $permissionMap[$permissionName],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Permissions seeded successfully!');
        $this->command->info('Roles: master, admin, kasir, siswa');
        $this->command->info('Total permissions: ' . count($permissions));
    }
}
