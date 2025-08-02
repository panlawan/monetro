{{-- resources/views/admin/roles/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('จัดการบทบาท') }}
            </h2>
            <a href="{{ route('admin.roles.create') }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i>เพิ่มบทบาทใหม่
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Roles Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        บทบาท
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        คำอธิบาย
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        สิทธิ์
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ผู้ใช้
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        สถานะ
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        การจัดการ
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($roles as $role)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full
                                                        @if($role->name === 'super_admin') bg-red-100 text-red-600
                                                        @elseif($role->name === 'admin') bg-purple-100 text-purple-600
                                                        @elseif($role->name === 'moderator') bg-yellow-100 text-yellow-600
                                                        @else bg-green-100 text-green-600
                                                        @endif">
                                                        <i class="fas fa-crown"></i>
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $role->display_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $role->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ $role->description ?: 'ไม่มีคำอธิบาย' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                @if($role->permissions && count($role->permissions) > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ count($role->permissions) }} สิทธิ์
                                                    </span>
                                                    <button onclick="showPermissions({{ $role->id }})" 
                                                            class="ml-2 text-blue-600 hover:text-blue-800 text-xs">
                                                        ดูรายละเอียด
                                                    </button>
                                                @else
                                                    <span class="text-gray-400 italic">ไม่มีสิทธิ์</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $role->users_count }} คน
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($role->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>ใช้งาน
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i>ปิดใช้งาน
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.roles.show', $role) }}" 
                                               class="text-indigo-600 hover:text-indigo-900" title="ดู">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="{{ route('admin.roles.edit', $role) }}" 
                                               class="text-blue-600 hover:text-blue-900" title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button onclick="editPermissions({{ $role->id }})" 
                                                    class="text-purple-600 hover:text-purple-900" title="จัดการสิทธิ์">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            
                                            @if($role->users_count == 0)
                                                <button onclick="deleteRole({{ $role->id }})" 
                                                        class="text-red-600 hover:text-red-900" title="ลบ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            ไม่พบข้อมูลบทบาท
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Permissions Modal --}}
    <div id="permissions-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">สิทธิ์ของบทบาท</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="permissions-content" class="mb-4">
                    <!-- Permissions will be loaded here -->
                </div>
                <div class="flex justify-end">
                    <button onclick="closeModal()" 
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show permissions modal
        function showPermissions(roleId) {
            const role = @json($roles->keyBy('id'));
            const roleData = role[roleId];
            
            document.getElementById('modal-title').textContent = `สิทธิ์ของ ${roleData.display_name}`;
            
            let content = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
            
            if (roleData.permissions && roleData.permissions.length > 0) {
                roleData.permissions.forEach(permission => {
                    content += `
                        <div class="flex items-center p-2 bg-blue-50 rounded">
                            <i class="fas fa-key text-blue-500 mr-2"></i>
                            <span class="text-sm">${permission}</span>
                        </div>
                    `;
                });
            } else {
                content += '<div class="text-gray-500 italic">ไม่มีสิทธิ์</div>';
            }
            
            content += '</div>';
            document.getElementById('permissions-content').innerHTML = content;
            document.getElementById('permissions-modal').classList.remove('hidden');
        }

        // Edit permissions (redirect to edit page)
        function editPermissions(roleId) {
            window.location.href = `/admin/roles/${roleId}/edit`;
        }

        // Close modal
        function closeModal() {
            document.getElementById('permissions-modal').classList.add('hidden');
        }

        // Delete role
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

        // Close modal when clicking outside
        document.getElementById('permissions-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</x-app-layout>