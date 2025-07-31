<?php
// app/Http/Controllers/Admin/RoleController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $permissions = [
        'users.create', 'users.read', 'users.update', 'users.delete',
        'roles.create', 'roles.read', 'roles.update', 'roles.delete',
        'content.create', 'content.read', 'content.update', 'content.delete',
        'settings.read', 'settings.update',
        'reports.read', 'reports.create',
    ];

    public function index()
    {
        $roles = Role::withCount('users')->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    public function show(Role $role)
    {
        $role->load('users');
        return view('admin.roles.show', compact('role'));
    }

    public function create()
    {
        $permissions = $this->permissions;
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.roles.index')
                        ->with('success', 'สร้าง Role สำเร็จ');
    }

    public function edit(Role $role)
    {
        $permissions = $this->permissions;
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.roles.index')
                        ->with('success', 'อัปเดต Role สำเร็จ');
    }

    public function destroy(Role $role)
    {
        // Prevent deleting if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                            ->with('error', 'ไม่สามารถลบ Role ที่มีผู้ใช้ได้');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
                        ->with('success', 'ลบ Role สำเร็จ');
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $role->update([
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->back()
                        ->with('success', 'อัปเดต Permissions สำเร็จ');
    }
}