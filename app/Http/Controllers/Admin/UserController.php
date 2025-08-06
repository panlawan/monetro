<?php

// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // เริ่ม query จาก User model
        $query = User::with('roles');

        // Debug: Log ค่าที่ได้รับ
        Log::info('Search parameters:', [
            'search' => $request->get('search'),
            'role' => $request->get('role'),
            'status' => $request->get('status'),
            'sort' => $request->get('sort'),
            'direction' => $request->get('direction'),
        ]);

        // Search functionality - แก้ไขให้ทำงานได้
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            Log::info('Searching for: '.$searchTerm);

            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%'.$searchTerm.'%')
                    ->orWhere('email', 'LIKE', '%'.$searchTerm.'%')
                    ->orWhere('phone', 'LIKE', '%'.$searchTerm.'%');
            });
        }

        // Filter by role - แก้ไขให้ทำงานได้
        if ($request->filled('role')) {
            $roleName = $request->role;
            Log::info('Filtering by role: '.$roleName);

            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        // Filter by status - แก้ไขให้ทำงานได้
        if ($request->has('status') && $request->status !== '' && $request->status !== null) {
            $status = (bool) $request->status;
            Log::info('Filtering by status: '.($status ? 'active' : 'inactive'));

            $query->where('is_active', $status);
        }

        // Debug: Count total before sorting
        $totalBeforeSort = $query->count();
        Log::info('Total users before sort: '.$totalBeforeSort);

        // Sorting - เพิ่มการเรียงลำดับ
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        // Validate sort fields
        $allowedSorts = ['name', 'email', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Debug: Log final SQL query
        Log::info('Final SQL query: '.$query->toSql());
        Log::info('Query bindings: ', $query->getBindings());

        // Paginate with query string
        $users = $query->paginate(15)->withQueryString();

        // Debug: Count final results
        Log::info('Final result count: '.$users->total());

        $roles = Role::where('is_active', true)->get();

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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $avatarPath]);
        }

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
            ->with('success', 'ผู้ใช้ถูกสร้างเรียบร้อยแล้ว');
    }

    public function edit(User $user)
    {
        $roles = Role::where('is_active', true)->get();
        $user->load('roles');

        // Prevent editing own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'คุณไม่สามารถแก้ไขบัญชีของตัวเองได้');
        }

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Prevent editing own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'คุณไม่สามารถแก้ไขบัญชีของตัวเองได้');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Handle avatar removal
        if ($request->has('remove_avatar') && $request->remove_avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
                $updateData['avatar'] = null;
            }
        }
        // Handle avatar upload
        elseif ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $avatarPath;
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
            ->with('success', 'ข้อมูลผู้ใช้ถูกอัปเดตเรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'คุณไม่สามารถลบบัญชีของตัวเองได้');
        }

        // Delete avatar file
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Detach roles
        $user->roles()->detach();

        // Delete user
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ผู้ใช้ถูกลบเรียบร้อยแล้ว');
    }

    public function activate(User $user)
    {
        Log::info("Activating user ID: {$user->id}");

        try {
            $user->update(['is_active' => true]);

            Log::info("User {$user->id} activated successfully");

            return response()->json([
                'success' => true,
                'message' => 'เปิดใช้งานบัญชีเรียบร้อยแล้ว',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'is_active' => $user->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to activate user {$user->id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเปิดใช้งานบัญชี',
            ], 500);
        }
    }

    public function deactivate(User $user)
    {
        Log::info("Deactivating user ID: {$user->id}");

        // Prevent deactivating own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'คุณไม่สามารถปิดใช้งานบัญชีของตัวเองได้',
            ], 400);
        }

        try {
            $user->update(['is_active' => false]);

            Log::info("User {$user->id} deactivated successfully");

            return response()->json([
                'success' => true,
                'message' => 'ปิดใช้งานบัญชีเรียบร้อยแล้ว',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'is_active' => $user->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to deactivate user {$user->id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการปิดใช้งานบัญชี',
            ], 500);
        }
    }

    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Prevent modifying own roles
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'คุณไม่สามารถแก้ไขบทบาทของตัวเองได้',
            ], 400);
        }

        $user->roles()->detach();

        if ($request->roles) {
            foreach ($request->roles as $roleId) {
                $user->roles()->attach($roleId, [
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตบทบาทเรียบร้อยแล้ว',
            'roles' => $user->fresh()->roles,
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = collect($request->user_ids)->reject(function ($id) {
            return $id == auth()->id(); // Exclude current user
        });

        $count = 0;

        switch ($request->action) {
            case 'activate':
                $count = User::whereIn('id', $userIds)->update(['is_active' => true]);
                $message = "เปิดใช้งาน {$count} บัญชีเรียบร้อยแล้ว";
                break;

            case 'deactivate':
                $count = User::whereIn('id', $userIds)->update(['is_active' => false]);
                $message = "ปิดใช้งาน {$count} บัญชีเรียบร้อยแล้ว";
                break;

            case 'delete':
                $users = User::whereIn('id', $userIds)->get();
                foreach ($users as $user) {
                    if ($user->avatar) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    $user->roles()->detach();
                    $user->delete();
                    $count++;
                }
                $message = "ลบ {$count} บัญชีเรียบร้อยแล้ว";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'count' => $count,
        ]);
    }

    public function impersonate(User $user)
    {
        // Only super admin can impersonate
        if (! auth()->user()->hasRole('super_admin')) {
            abort(403, 'ไม่มีสิทธิ์ในการใช้ฟีเจอร์นี้');
        }

        // Cannot impersonate self
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'ไม่สามารถปลอมตัวเป็นตัวเองได้');
        }

        // Store original user ID in session
        session(['impersonator' => auth()->id()]);

        // Login as the target user
        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', "คุณกำลังปลอมตัวเป็น {$user->name}");
    }

    public function stopImpersonating(Request $request)
    {

        if (! session()->has('impersonator')) {
            return redirect()->route('dashboard')->with('error', 'คุณไม่ได้อยู่ในสถานะปลอมตัว');
        }

        Log::info('Stop impersonating called', [
            'session_data' => session()->all(),
            'current_user' => auth()->id(),
            'has_impersonator' => session()->has('impersonator'),
        ]);

        if (session()->has('impersonator')) {
            $originalUserId = session('impersonator');

            // ลบ session ก่อน
            session()->forget('impersonator');

            // หา original user
            $originalUser = User::find($originalUserId);

            if ($originalUser) {
                // Login กลับเป็น original user
                auth()->login($originalUser);

                Log::info('Successfully stopped impersonating', [
                    'original_user_id' => $originalUserId,
                    'current_user_id' => auth()->id(),
                ]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'หยุดการปลอมตัวเรียบร้อยแล้ว');
            } else {
                Log::error('Original user not found', ['user_id' => $originalUserId]);

                // ถ้าหา original user ไม่เจอ ให้ logout
                auth()->logout();

                return redirect()->route('login')
                    ->with('error', 'เกิดข้อผิดพลาด กรุณาเข้าสู่ระบบใหม่');
            }
        } else {
            Log::warning('Stop impersonating called but no impersonator session');

            // ถ้าไม่มี impersonator session ให้กลับไปหน้า admin
            return redirect()->route('admin.users.index')
                ->with('info', 'คุณไม่ได้อยู่ในสถานะปลอมตัว');
        }
    }
}
