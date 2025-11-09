<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions
     */
    public function index()
    {
        $this->authorize('permissions.view');
        
        $permissions = Permission::withCount('roles')->get()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });
        
        return view('pages.permissions.index', compact('permissions'));
    }
}

