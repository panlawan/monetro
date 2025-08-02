{{-- resources/views/admin/users/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('ข้อมูลผู้ใช้: ' . $user->name) }}
            </h2>
            <div class="flex space-x-2">
                @if($user->id !== auth()->id())
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-edit mr-2"></i>แก้ไข
                    </a>
                    
                    @if(auth()->user()->hasRole('super_admin'))
                        <button onclick="impersonateUser({{ $user->id }})" 
                                class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-user-secret mr-2"></i>ปลอมตัว
                        </button>
                    @endif
                @endif
                
                <a href="{{ route('admin.users.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>กลับ
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Profile Section --}}
                        <div class="md:col-span-1">
                            <div class="bg-gray-50 rounded-lg p-6 text-center">
                                {{-- Avatar --}}
                                <div class="mb-4">
                                    @if($user->avatar)
                                        <img class="mx-auto h-32 w-32 rounded-full object-cover border-4 border-white shadow-lg" 
                                             src="{{ Storage::url($user->avatar) }}" 
                                             alt="{{ $user->name }}">
                                    @else
                                        <div class="mx-auto h-32 w-32 rounded-full bg-gray-300 flex items-center justify-center border-4 border-white shadow-lg">
                                            <span class="text-3xl font-bold text-gray-600">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Basic Info --}}
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $user->name }}</h3>
                                <p class="text-gray-600 mb-4">{{ $user->email }}</p>

                                {{-- Status Badge --}}
                                <div class="mb-4">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-2"></i>ใช้งาน
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-2"></i>ปิดใช้งาน
                                        </span>
                                    @endif
                                </div>

                                {{-- Quick Actions --}}
                                @if($user->id !== auth()->id())
                                    <div class="space-y-2">
                                        <button onclick="toggleUserStatus({{ $user->id }}, {{ $user->is_active ? 'false' : 'true' }})"
                                                class="w-full {{ $user->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white font-bold py-2 px-4 rounded">
                                            <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }} mr-2"></i>
                                            {{ $user->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}
                                        </button>
                                        
                                        <button onclick="deleteUser({{ $user->id }})" 
                                                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                            <i class="fas fa-trash mr-2"></i>ลบผู้ใช้
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Details Section --}}
                        <div class="md:col-span-2 space-y-6">
                            {{-- Personal Information --}}
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-user mr-2 text-blue-500"></i>ข้อมูลส่วนตัว
                                </h4>
                                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ชื่อ-นามสกุล</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">อีเมล</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">เบอร์โทรศัพท์</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $user->phone ?: 'ไม่ระบุ' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">สถานะอีเมล</dt>
                                        <dd class="mt-1 text-sm">
                                            @if($user->email_verified_at)
                                                <span class="text-green-600">
                                                    <i class="fas fa-check-circle mr-1"></i>ยืนยันแล้ว ({{ $user->email_verified_at->format('d/m/Y') }})
                                                </span>
                                            @else
                                                <span class="text-red-600">
                                                    <i class="fas fa-times-circle mr-1"></i>ยังไม่ยืนยัน
                                                </span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Roles & Permissions --}}
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-shield-alt mr-2 text-purple-500"></i>บทบาทและสิทธิ์
                                </h4>
                                
                                {{-- Current Roles --}}
                                <div class="mb-4">
                                    <dt class="text-sm font-medium text-gray-500 mb-2">บทบาทปัจจุบัน</dt>
                                    <dd class="flex flex-wrap gap-2">
                                        @forelse($user->roles as $role)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                @if($role->name === 'super_admin') bg-red-100 text-red-800
                                                @elseif($role->name === 'admin') bg-purple-100 text-purple-800
                                                @elseif($role->name === 'moderator') bg-yellow-100 text-yellow-800
                                                @else bg-green-100 text-green-800
                                                @endif">
                                                <i class="fas fa-crown mr-1"></i>{{ $role->display_name }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400 italic">ไม่มีบทบาท</span>
                                        @endforelse
                                    </dd>
                                </div>

                                {{-- Permissions --}}
                                @if($user->roles->isNotEmpty())
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 mb-2">สิทธิ์การใช้งาน</dt>
                                        <dd>
                                            @php
                                                $allPermissions = $user->roles->flatMap(function($role) {
                                                    return $role->permissions;
                                                })->unique()->sort();
                                            @endphp
                                            
                                            @if($allPermissions->isNotEmpty())
                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                                    @foreach($allPermissions as $permission)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            <i class="fas fa-key mr-1"></i>{{ $permission }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">ไม่มีสิทธิ์เฉพาะ</span>
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </div>

                            {{-- Account Activity --}}
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-history mr-2 text-green-500"></i>กิจกรรมบัญชี
                                </h4>
                                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">สร้างบัญชีเมื่อ</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $user->created_at->format('d/m/Y H:i:s') }}
                                            <span class="text-gray-500">({{ $user->created_at->diffForHumans() }})</span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">อัปเดตล่าสุด</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $user->updated_at->format('d/m/Y H:i:s') }}
                                            <span class="text-gray-500">({{ $user->updated_at->diffForHumans() }})</span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Role Assignment History --}}
                            @if($user->roles->isNotEmpty())
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-timeline mr-2 text-orange-500"></i>ประวัติการมอบหมายบทบาท
                                    </h4>
                                    <div class="space-y-3">
                                        @foreach($user->roles as $role)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center space-x-3">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($role->name === 'super_admin') bg-red-100 text-red-800
                                                        @elseif($role->name === 'admin') bg-purple-100 text-purple-800
                                                        @elseif($role->name === 'moderator') bg-yellow-100 text-yellow-800
                                                        @else bg-green-100 text-green-800
                                                        @endif">
                                                        {{ $role->display_name }}
                                                    </span>
                                                    <span class="text-sm text-gray-600">{{ $role->description }}</span>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    @if($role->pivot->assigned_at)
                                                        มอบหมายเมื่อ: {{ \Carbon\Carbon::parse($role->pivot->assigned_at)->format('d/m/Y H:i') }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle user status
        function toggleUserStatus(userId, newStatus) {
            const url = newStatus === 'true' 
                ? `/admin/users/${userId}/activate`
                : `/admin/users/${userId}/deactivate`;
                
            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        }

        // Delete user
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