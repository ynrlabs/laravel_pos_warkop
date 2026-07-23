<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Outlet management
            'view outlets',
            'create outlets',
            'edit outlets',
            'delete outlets',
            
            // Category management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            
            // Product management
            'view products',
            'create products',
            'edit products',
            'delete products',
            'manage product variants',
            'manage product modifiers',
            'manage product toppings',
            
            // Modifier management
            'view modifiers',
            'create modifiers',
            'edit modifiers',
            'delete modifiers',
            
            // Topping management
            'view toppings',
            'create toppings',
            'edit toppings',
            'delete toppings',
            
            // POS operations
            'access pos',
            'create orders',
            'view orders',
            'edit orders',
            'void orders',
            'refund orders',
            'apply discounts',
            'open tabs',
            'manage tabs',
            'split bills',
            'merge tabs',
            'transfer tabs',
            
            // Payment
            'process payments',
            'process cash payments',
            'process qris payments',
            'process ewallet payments',
            'process split payments',
            'manage debt/credit',
            
            // Shift management
            'open shift',
            'close shift',
            'view shift reports',
            'manage shift cash',
            
            // KDS
            'access kds',
            'manage kds stations',
            'update kds status',
            
            // Inventory
            'view inventory',
            'manage ingredients',
            'manage recipes',
            'manage stock movements',
            'manage purchase orders',
            'manage suppliers',
            'manage stock opname',
            'manage waste logs',
            
            // Reports
            'view reports',
            'view sales reports',
            'view inventory reports',
            'view profit loss reports',
            'view payment mix reports',
            'view kasir performance reports',
            'export reports',
            
            // Settings
            'manage settings',
            'manage printers',
            'manage tax rates',
            'manage rounding rules',
            'manage discounts',
            'manage integrations',
            
            // Users & Roles
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',
            'manage permissions',
            
            // Audit & Logs
            'view activity logs',
            'view notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Define roles with permissions
        $roles = [
            'super_admin' => [
                'permissions' => '*', // All permissions
                'description' => 'Full system access including multi-outlet management',
            ],
            
            'manager' => [
                'permissions' => [
                    'view outlets',
                    'view categories',
                    'create categories',
                    'edit categories',
                    'view products',
                    'create products',
                    'edit products',
                    'manage product variants',
                    'manage product modifiers',
                    'manage product toppings',
                    'view modifiers',
                    'create modifiers',
                    'edit modifiers',
                    'view toppings',
                    'create toppings',
                    'edit toppings',
                    'access pos',
                    'create orders',
                    'view orders',
                    'edit orders',
                    'void orders',
                    'refund orders',
                    'apply discounts',
                    'open tabs',
                    'manage tabs',
                    'split bills',
                    'merge tabs',
                    'transfer tabs',
                    'process payments',
                    'process cash payments',
                    'process qris payments',
                    'process ewallet payments',
                    'process split payments',
                    'manage debt/credit',
                    'open shift',
                    'close shift',
                    'view shift reports',
                    'manage shift cash',
                    'access kds',
                    'manage kds stations',
                    'update kds status',
                    'view inventory',
                    'manage ingredients',
                    'manage recipes',
                    'manage stock movements',
                    'manage purchase orders',
                    'manage suppliers',
                    'manage stock opname',
                    'manage waste logs',
                    'view reports',
                    'view sales reports',
                    'view inventory reports',
                    'view profit loss reports',
                    'view payment mix reports',
                    'view kasir performance reports',
                    'export reports',
                    'manage settings',
                    'manage printers',
                    'manage tax rates',
                    'manage rounding rules',
                    'manage discounts',
                    'view users',
                    'create users',
                    'edit users',
                    'assign roles',
                    'view activity logs',
                    'view notifications',
                ],
                'description' => 'Manajer outlet - akses penuh operasional outlet',
            ],
            
            'kasir' => [
                'permissions' => [
                    'view categories',
                    'view products',
                    'access pos',
                    'create orders',
                    'view orders',
                    'edit orders',
                    'void orders',
                    'apply discounts',
                    'open tabs',
                    'manage tabs',
                    'split bills',
                    'merge tabs',
                    'transfer tabs',
                    'process payments',
                    'process cash payments',
                    'process qris payments',
                    'process ewallet payments',
                    'process split payments',
                    'manage debt/credit',
                    'open shift',
                    'close shift',
                    'view shift reports',
                    'manage shift cash',
                ],
                'description' => 'Kasir - transaksi POS, pembayaran, shift',
            ],
            
            'barista' => [
                'permissions' => [
                    'access kds',
                    'update kds status',
                ],
                'description' => 'Barista - KDS station minuman',
            ],
            
            'koki' => [
                'permissions' => [
                    'access kds',
                    'update kds status',
                ],
                'description' => 'Koki/Dapur - KDS station makanan',
            ],
            
            'gudang' => [
                'permissions' => [
                    'view inventory',
                    'manage ingredients',
                    'manage stock movements',
                    'manage purchase orders',
                    'manage suppliers',
                    'manage stock opname',
                    'manage waste logs',
                    'view inventory reports',
                ],
                'description' => 'Gudang - manajemen stok & pembelian',
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            
            if ($roleData['permissions'] === '*') {
                $role->syncPermissions(Permission::all());
            } else {
                $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
                $role->syncPermissions($permissions);
            }
        }

        $this->command->info('Permissions and Roles seeded successfully!');
    }
}