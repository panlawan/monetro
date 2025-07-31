<?php

// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        // Filter by role
        if ($request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status);
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        $roles = Role::where('is_active', true)->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(), // Auto verify admin-created users
        ]);

        // Assign roles
        if ($request->roles) {
            foreach ($request->roles as $roleId) {
                $user->roles()->attach($roleId, [
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'สร้างผู้ใช้สำเร็จ');
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::where('is_active', true)->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Update roles
        $user->roles()->detach();
        if ($request->roles) {
            foreach ($request->roles as $roleId) {
                $user->roles()->attach($roleId, [
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'อัปเดตผู้ใช้สำเร็จ');
    }

    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ลบผู้ใช้สำเร็จ');
    }

    public function activate(User $user)
    {
        $user->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'เปิดใช้งานบัญชีสำเร็จ');
    }

    public function deactivate(User $user)
    {
        // Prevent deactivating own account
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'ไม่สามารถปิดใช้งานบัญชีของตัวเองได้');
        }

        $user->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'ปิดใช้งานบัญชีสำเร็จ');
    }

    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->roles()->detach();

        if ($request->roles) {
            foreach ($request->roles as $roleId) {
                $user->roles()->attach($roleId, [
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->back()
            ->with('success', 'อัปเดต Role สำเร็จ');
    }
}
