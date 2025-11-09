<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    DB::table('roles')->delete();
    DB::table('permissions')->delete();

    // Create permissions
    $permissions = [
      // Users permissions
      'users.view',
      'users.create',
      'users.edit',
      'users.delete',

      // Roles permissions
      'roles.view',
      'roles.create',
      'roles.edit',
      'roles.delete',

      // Permissions management
      'permissions.view',
      'permissions.manage',

      // Countries permissions
      'countries.view',
      'countries.create',
      'countries.edit',
      'countries.delete',

      // Cities permissions
      'cities.view',
      'cities.create',
      'cities.edit',
      'cities.delete',

      // Categories permissions
      'categories.view',
      'categories.create',
      'categories.edit',
      'categories.delete',

      // Services permissions
      'services.view',
      'services.create',
      'services.edit',
      'services.delete',
      'services.manage',

      // Products permissions
      'products.view',
      'products.create',
      'products.edit',
      'products.delete',
      'products.manage',

      // Dashboard
      'dashboard.view',
    ];

    foreach ($permissions as $permission) {
      Permission::create(['name' => $permission]);
    }

    // Create roles and assign permissions
    $superAdmin = Role::create(['name' => 'Super Admin']);
    $superAdmin->givePermissionTo(Permission::all());

    // Assign Super Admin role to existing admin users
    $adminUsers = User::where('type', 'admin')->get();
    foreach ($adminUsers as $user) {
      $user->assignRole('Super Admin');
    }
  }
}
