{{-- resources/views/admin/users/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('จัดการผู้ใช้') }}
            </h2>
            <a href="{{ route('admin.users.create') }}" 
               class="btn btn-success btn-lg">
                <i class="fas fa-plus mr-2"></i>เพิ่มผู้ใช้ใหม่
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Impersonating Notice --}}
            @if(session()->has('impersonator'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4 flex justify-between items-center">
                    <span>🎭 คุณกำลังปลอมตัวเป็น {{ auth()->user()->name }}</span>
                    <a href="{{ route('stop-impersonating') }}" 
                    class="btn btn-warning btn-sm">
                        หยุดการปลอมตัว
                    </a>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Search and Filter Form --}}
                    <form method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="flex flex-col items-center space-y-4">
                            {{-- Input Fields Row --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full max-w-4xl">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                                    <input type="text" name="search" value="{{ request('search') }}" 
                                           placeholder="ชื่อ, อีเมล, เบอร์โทร..."
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">บทบาท</label>
                                    <select name="role" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">ทุกบทบาท</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                                {{ $role->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                                    <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">ทุกสถานะ</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>ใช้งาน</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>ปิดใช้งาน</option>
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Buttons Row --}}
                            <div class="flex items-center justify-center space-x-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-search mr-2"></i>ค้นหา
                                </button>
                                
                                {{-- Clear Button --}}
                                @if(request()->hasAny(['search', 'role', 'status', 'sort', 'direction']))
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-undo mr-2"></i>ล้างการค้นหา
                                    </a>
                                @else
                                    <button type="button" onclick="clearForm()" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-undo mr-2"></i>ล้าง
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>

                    {{-- Debug Section (เอาออกในโปรดักชัน) --}}
                    <!-- @if(config('app.debug'))
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                            <h4 class="font-semibold text-yellow-800">Debug Info:</h4>
                            <p class="text-sm text-yellow-700">
                                <strong>Search:</strong> {{ request('search') ?? 'ว่าง' }}<br>
                                <strong>Role:</strong> {{ request('role') ?? 'ว่าง' }}<br>
                                <strong>Status:</strong> {{ request('status') ?? 'ว่าง' }}<br>
                                <strong>Sort:</strong> {{ request('sort', 'created_at') }}<br>
                                <strong>Direction:</strong> {{ request('direction', 'desc') }}<br>
                                <strong>Total Users:</strong> {{ $users->total() }}
                            </p>
                        </div>
                    @endif -->

                    {{-- Search Results Info --}}
                    @if(request()->hasAny(['search', 'role', 'status']))
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-blue-800">ผลการค้นหา</h4>
                                    <p class="text-sm text-blue-600">
                                        พบ {{ $users->total() }} รายการ
                                        @if(request('search'))
                                            สำหรับ "{{ request('search') }}"
                                        @endif
                                        @if(request('role'))
                                            ในบทบาท "{{ $roles->where('name', request('role'))->first()?->display_name }}"
                                        @endif
                                        @if(request('status') !== null)
                                            สถานะ "{{ request('status') == '1' ? 'ใช้งาน' : 'ปิดใช้งาน' }}"
                                        @endif
                                    </p>
                                </div>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm">
                                    แสดงทั้งหมด
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Bulk Actions --}}
                    <div class="mb-4 flex items-center space-x-4" id="bulk-actions" style="display: none;">
                        <span class="text-sm text-gray-600">เลือกแล้ว: <span id="selected-count">0</span> รายการ</span>
                        <button onclick="bulkAction('activate')" class="btn btn-success btn-sm">
                            เปิดใช้งาน
                        </button>
                        <button onclick="bulkAction('deactivate')" class="btn btn-warning btn-sm">
                            ปิดใช้งาน
                        </button>
                        <button onclick="bulkAction('delete')" class="btn btn-danger btn-sm">
                            ลบ
                        </button>
                    </div>

                    {{-- Users Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input type="checkbox" id="select-all" class="rounded border-gray-300">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'sort' => 'name', 
                                            'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'
                                        ]) }}">
                                            ผู้ใช้
                                            @if(request('sort') === 'name')
                                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 text-gray-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        บทบาท
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        สถานะ
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'sort' => 'created_at', 
                                            'direction' => request('sort') === 'created_at' && request('direction') === 'asc' ? 'desc' : 'asc'
                                        ]) }}">
                                            วันที่สร้าง
                                            @if(request('sort') === 'created_at')
                                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 text-gray-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        การจัดการ
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr class="hover:bg-gray-50 {{ $user->id === auth()->id() ? 'bg-blue-50' : '' }}">
                                        <td class="px-6 py-4">
                                            @if($user->id !== auth()->id())
                                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                                       class="user-checkbox rounded border-gray-300">
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @if($user->avatar)
                                                        <img class="h-10 w-10 rounded-full object-cover" 
                                                             src="{{ Storage::url($user->avatar) }}" 
                                                             alt="{{ $user->name }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-gray-700">
                                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $user->name }}
                                                        @if($user->id === auth()->id())
                                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full ml-2">คุณ</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                    @if($user->phone)
                                                        <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @forelse($user->roles as $role)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-1 mb-1
                                                    @if($role->name === 'super_admin') bg-red-100 text-red-800
                                                    @elseif($role->name === 'admin') bg-purple-100 text-purple-800
                                                    @elseif($role->name === 'moderator') bg-yellow-100 text-yellow-800
                                                    @else bg-green-100 text-green-800
                                                    @endif">
                                                    {{ $role->display_name }}
                                                </span>
                                            @empty
                                                <span class="text-gray-400 italic">ไม่มีบทบาท</span>
                                            @endforelse
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <button onclick="toggleUserStatus({{ $user->id }}, {{ $user->is_active ? 'false' : 'true' }})"
                                                        class="toggle-status {{ $user->is_active ? 'bg-green-600' : 'bg-gray-200' }} relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                        {{ $user->id === auth()->id() ? 'disabled title="ไม่สามารถเปลี่ยนสถานะของตัวเองได้"' : '' }}
                                                        data-user-id="{{ $user->id }}"
                                                        data-current-status="{{ $user->is_active ? 'true' : 'false' }}">
                                                    <span class="sr-only">Toggle user status</span>
                                                    <span class="{{ $user->is_active ? 'translate-x-5' : 'translate-x-0' }} pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                                </button>
                                                <span class="ml-3 text-sm {{ $user->is_active ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $user->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.users.show', $user) }}" 
                                               class="btn btn-sm" title="ดู">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($user->id !== auth()->id())
                                                <a href="{{ route('admin.users.edit', $user) }}" 
                                                   class="btn btn-primary btn-sm" title="แก้ไข">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                @if(auth()->user()->hasRole('super_admin'))
                                                    <button onclick="impersonateUser({{ $user->id }})" 
                                                            class="btn btn-warning btn-sm" title="ปลอมตัว">
                                                        <i class="fas fa-user-secret"></i>
                                                    </button>
                                                @endif
                                                
                                                <button onclick="deleteUser({{ $user->id }})" 
                                                        class="btn btn-danger btn-sm" title="ลบ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            @if(request()->hasAny(['search', 'role', 'status']))
                                                <div class="flex flex-col items-center py-8">
                                                    <i class="fas fa-search text-gray-300 text-4xl mb-4"></i>
                                                    <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่พบผลการค้นหา</h3>
                                                    <p class="text-gray-500 mb-4">ลองเปลี่ยนคำค้นหาหรือตัวกรองอื่น</p>
                                                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                                                        แสดงผู้ใช้ทั้งหมด
                                                    </a>
                                                </div>
                                            @else
                                                <div class="flex flex-col items-center py-8">
                                                    <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                                                    <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่มีผู้ใช้</h3>
                                                    <p class="text-gray-500 mb-4">เริ่มต้นด้วยการเพิ่มผู้ใช้คนแรก</p>
                                                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                                                        <i class="fas fa-plus mr-2"></i>เพิ่มผู้ใช้ใหม่
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($users->hasPages())
                        <div class="mt-6">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    แสดง {{ $users->firstItem() }} ถึง {{ $users->lastItem() }} จาก {{ $users->total() }} รายการ
                                </div>
                                <div>
                                    {{ $users->withQueryString()->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
        // Clear form function
        function clearForm() {
            document.querySelector('input[name="search"]').value = '';
            document.querySelector('select[name="role"]').selectedIndex = 0;
            document.querySelector('select[name="status"]').selectedIndex = 0;
        }

        // Select All Functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        // Individual checkbox change
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');
            
            selectedCount.textContent = checkedBoxes.length;
            bulkActions.style.display = checkedBoxes.length > 0 ? 'flex' : 'none';
        }

        // Toggle user status function
        function toggleUserStatus(userId, newStatus) {
            console.log('Toggling user:', userId, 'to status:', newStatus);
            
            const url = newStatus === 'true' || newStatus === true
                ? `/admin/users/${userId}/activate`
                : `/admin/users/${userId}/deactivate`;
                
            console.log('Request URL:', url);
            
            // Show loading state
            const toggleButton = document.querySelector(`button[onclick*="${userId}"]`);
            if (toggleButton) {
                toggleButton.disabled = true;
                toggleButton.style.opacity = '0.6';
            }
            
            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'เกิดข้อผิดพลาด', 'error');
                    
                    // Re-enable button
                    if (toggleButton) {
                        toggleButton.disabled = false;
                        toggleButton.style.opacity = '1';
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                
                // Re-enable button
                if (toggleButton) {
                    toggleButton.disabled = false;
                    toggleButton.style.opacity = '1';
                }
            });
        }

        // Show notification function
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = `notification fixed top-4 right-4 px-6 py-3 rounded shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Bulk actions
        function bulkAction(action) {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            const userIds = Array.from(checkedBoxes).map(cb => cb.value);
            
            if (userIds.length === 0) {
                showNotification('กรุณาเลือกผู้ใช้อย่างน้อย 1 คน', 'error');
                return;
            }

            let confirmMessage = '';
            switch(action) {
                case 'activate':
                    confirmMessage = `คุณต้องการเปิดใช้งานบัญชี ${userIds.length} บัญชีใช่หรือไม่?`;
                    break;
                case 'deactivate':
                    confirmMessage = `คุณต้องการปิดใช้งานบัญชี ${userIds.length} บัญชีใช่หรือไม่?`;
                    break;
                case 'delete':
                    confirmMessage = `คุณต้องการลบบัญชี ${userIds.length} บัญชีใช่หรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้`;
                    break;
            }

            if (!confirm(confirmMessage)) return;

            fetch('/admin/users/bulk-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    user_ids: userIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'เกิดข้อผิดพลาด', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            });
        }

        // Delete single user
        function deleteUser(userId) {
            if (!confirm('คุณต้องการลบผู้ใช้นี้ใช่หรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}`;
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_token';
            csrfField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(methodField);
            form.appendChild(csrfField);
            document.body.appendChild(form);
            form.submit();
        }

        // Impersonate user
        function impersonateUser(userId) {
            if (!confirm('คุณต้องการปลอมตัวเป็นผู้ใช้นี้ใช่หรือไม่?')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}/impersonate`;
            
            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_token';
            csrfField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(csrfField);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</x-app-layout>