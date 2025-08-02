{{-- resources/views/admin/roles/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('รายละเอียดบทบาท: ' . $role->display_name) }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.roles.edit', $role) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-edit mr-2"></i>แก้ไข
                </a>
                <a href="{{ route('admin.roles.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>กลับ
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Role Information Card --}}
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            {{-- Role Icon --}}
                            <div class="text-center mb-6">
                                <div class="inline-flex items-center justify-center h-20 w-20 rounded-full mb-4
                                    @if($role->name === 'super_admin') bg-red-100 text-red-600
                                    @elseif($role->name === 'admin') bg-purple-100 text-purple-600
                                    @elseif($role->name === 'moderator') bg-yellow-100 text-yellow-600
                                    @else bg-green-100 text-green-600
                                    @endif">
                                    <i class="fas fa-crown text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">{{ $role->display_name }}</h3>
                                <p class="text-gray-600">{{ $role->name }}</p>
                            </div>

                            {{-- Status Badge --}}
                            <div class="text-center mb-6">
                                @if($role->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-2"></i>ใช้งาน
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-2"></i>ปิดใช้งาน
                                    </span>
                                @endif
                            </div>

                            {{-- Description --}}
                            @if($role->description)
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">คำอธิบาย</h4>
                                    <p class="text-sm text-gray-600">{{ $role->description }}</p>
                                </div>
                            @endif

                            {{-- Statistics --}}
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">สถิติ</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">ผู้ใช้</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $role->users->count() }} คน</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">สิทธิ์</span>
                                        <span class="text-sm font-medium text-gray-900">{{ count($role->permissions ?? []) }} สิทธิ์</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">สร้างเมื่อ</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $role->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Quick Actions --}}
                            <div class="mt-6 space-y-2">
                                <a href="{{ route('admin.roles.edit', $role) }}" 
                                   class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded flex items-center justify-center">
                                    <i class="fas fa-edit mr-2"></i>แก้ไขบทบาท
                                </a>
                                
                                @if($role->users->count() == 0)
                                    <button onclick="deleteRole({{ $role->id }})" 
                                            class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded flex items-center justify-center">
                                        <i class="fas fa-trash mr-2"></i>ลบบทบาท
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Permissions Section --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-key mr-2 text-blue-500"></i>สิทธิ์การใช้งาน
                            </h4>
                            
                            @if($role->permissions && count($role->permissions) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($role->permissions as $permission)
                                        <div class="flex items-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                                            <i class="fas fa-check text-blue-500 mr-3"></i>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $permission }}</div>
                                                <div class="text-xs text-gray-500">
                                                    @switch($permission)
                                                        @case('users.create')
                                                            สร้างผู้ใช้ใหม่
                                                            @break
                                                        @case('users.read')
                                                            ดูข้อมูลผู้ใช้
                                                            @break
                                                        @case('users.update')
                                                            แก้ไขข้อมูลผู้ใช้
                                                            @break
                                                        @case('users.delete')
                                                            ลบผู้ใช้
                                                            @break
                                                        @case('roles.create')
                                                            สร้างบทบาทใหม่
                                                            @break
                                                        @case('roles.read')
                                                            ดูข้อมูลบทบาท
                                                            @break
                                                        @case('roles.update')
                                                            แก้ไขบทบาท
                                                            @break
                                                        @case('roles.delete')
                                                            ลบบทบาท
                                                            @break
                                                        @default
                                                            {{ $permission }}
                                                    @endswitch
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-key text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500">ไม่มีสิทธิ์เฉพาะ</p>
                                    <a href="{{ route('admin.roles.edit', $role) }}" 
                                       class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-plus mr-2"></i>เพิ่มสิทธิ์
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Users with this Role --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-users mr-2 text-green-500"></i>ผู้ใช้ที่มีบทบาทนี้
                                <span class="ml-2 bg-gray-100 text-gray-800 text-sm px-2 py-1 rounded-full">
                                    {{ $role->users->count() }}
                                </span>
                            </h4>
                            
                            @if($role->users->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($role->users as $user)
                                        <div class="flex items-center p-4 bg-gray-50 rounded-lg border">
                                            <div class="flex-shrink-0">
                                                @if($user->avatar)
                                                    <img class="h-12 w-12 rounded-full object-cover" 
                                                         src="{{ Storage::url($user->avatar) }}" 
                                                         alt="{{ $user->name }}">
                                                @else
                                                    <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-600">
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                <div class="flex items-center mt-1">
                                                    @if($user->is_active)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            ใช้งาน
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                            ปิดใช้งาน
                                                        </span>
                                                    @endif
                                                    <span class="ml-2 text-xs text-gray-500">
                                                        สมาชิกเมื่อ {{ $user->created_at->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <a href="{{ route('admin.users.show', $user) }}" 
                                                   class="text-indigo-600 hover:text-indigo-800" title="ดูข้อมูลผู้ใช้">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500">ไม่มีผู้ใช้ที่มีบทบาทนี้</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Role History/Activity --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-history mr-2 text-orange-500"></i>ประวัติการเปลี่ยนแปลง
                            </h4>
                            
                            <div class="space-y-4">
                                <div class="border-l-4 border-blue-400 pl-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">สร้างบทบาท</p>
                                            <p class="text-sm text-gray-500">บทบาท "{{ $role->display_name }}" ถูกสร้างขึ้น</p>
                                        </div>
                                        <span class="text-sm text-gray-500">{{ $role->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                                
                                @if($role->updated_at != $role->created_at)
                                    <div class="border-l-4 border-green-400 pl-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">อัปเดตล่าสุด</p>
                                                <p class="text-sm text-gray-500">บทบาทถูกแก้ไขล่าสุด</p>
                                            </div>
                                            <span class="text-sm text-gray-500">{{ $role->updated_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Delete role function
        function deleteRole(roleId) {
            if (!confirm('คุณต้องการลบบทบาทนี้ใช่หรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/roles/${roleId}`;
            
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
    </script>
</x-app-layout>