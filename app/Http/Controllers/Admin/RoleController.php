<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $this->authorize('roles.view');
        
        $roles = Role::with('permissions')->withCount('users')->paginate(20);
        

        return view('pages.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $this->authorize('roles.create');
        
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });
        
        return view('pages.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $this->authorize('roles.create');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', __('dashboard.Role created successfully'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $this->authorize('roles.edit');
        
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });
        
        $role->load('permissions');
        
        return view('pages.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $this->authorize('roles.edit');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);
        
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', __('dashboard.Role updated successfully'));
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        $this->authorize('roles.delete');
        
        // Prevent deleting Super Admin role
        if ($role->name === 'Super Admin') {
            return redirect()
                ->route('roles.index')
                ->with('error', __('dashboard.Cannot delete Super Admin role'));
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', __('dashboard.Role deleted successfully'));
    }
}

