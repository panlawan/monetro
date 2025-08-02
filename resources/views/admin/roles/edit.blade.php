{{-- resources/views/admin/roles/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('แก้ไขบทบาท: ' . $role->display_name) }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.roles.show', $role) }}" 
                   class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-eye mr-2"></i>ดูข้อมูล
                </a>
                <a href="{{ route('admin.roles.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>กลับ
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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
                    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Left Column --}}
                            <div class="space-y-6">
                                {{-- Name --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">ชื่อบทบาท (ภาษาอังกฤษ) *</label>
                                    <input type="text" name="name" id="name" 
                                           value="{{ old('name', $role->name) }}" required
                                           placeholder="เช่น moderator, editor"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <p class="mt-1 text-sm text-gray-500">ใช้ตัวอักษรเล็ก ไม่มีช่องว่าง และขีดล่าง</p>
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Display Name --}}
                                <div>
                                    <label for="display_name" class="block text-sm font-medium text-gray-700">ชื่อแสดง *</label>
                                    <input type="text" name="display_name" id="display_name" 
                                           value="{{ old('display_name', $role->display_name) }}" required
                                           placeholder="เช่น ผู้ดูแลเนื้อหา, บรรณาธิการ"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('display_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">คำอธิบาย</label>
                                    <textarea name="description" id="description" rows="4"
                                              placeholder="อธิบายหน้าที่และความรับผิดชอบของบทบาทนี้"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $role->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" 
                                               {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">เปิดใช้งานบทบาท</span>
                                    </label>
                                </div>

                                {{-- Role Stats --}}
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">สถิติบทบาท</h4>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <div><strong>ผู้ใช้ที่มีบทบาทนี้:</strong> {{ $role->users->count() }} คน</div>
                                        <div><strong>สิทธิ์ปัจจุบัน:</strong> {{ count($role->permissions ?? []) }} สิทธิ์</div>
                                        <div><strong>สร้างเมื่อ:</strong> {{ $role->created_at->format('d/m/Y H:i:s') }}</div>
                                        <div><strong>อัปเดตล่าสุด:</strong> {{ $role->updated_at->format('d/m/Y H:i:s') }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column - Permissions --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">สิทธิ์การใช้งาน</label>
                                <div class="border border-gray-200 rounded-md p-4 max-h-96 overflow-y-auto">
                                    {{-- Select All --}}
                                    <div class="mb-4 pb-4 border-b border-gray-200">
                                        <label class="flex items-center font-medium">
                                            <input type="checkbox" id="select-all-permissions" 
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <span class="ml-2 text-sm text-indigo-600">เลือกทั้งหมด</span>
                                        </label>
                                    </div>

                                    {{-- Permission Groups --}}
                                    @foreach($permissions as $group => $groupPermissions)
                                        <div class="mb-6">
                                            <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                                <i class="fas fa-folder mr-2 text-blue-500"></i>{{ $group }}
                                            </h4>
                                            <div class="space-y-2 ml-6">
                                                @foreach($groupPermissions as $permission => $description)
                                                    @php
                                                        $isChecked = old('permissions') 
                                                            ? collect(old('permissions'))->contains($permission)
                                                            : collect($role->permissions ?? [])->contains($permission);
                                                    @endphp
                                                    <label class="flex items-start space-x-2">
                                                        <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                                               {{ $isChecked ? 'checked' : '' }}
                                                               class="permission-checkbox mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">{{ $description }}</div>
                                                            <div class="text-xs text-gray-500">{{ $permission }}</div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('permissions')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Current Users with this Role --}}
                        @if($role->users->isNotEmpty())
                            <div class="mt-8">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">ผู้ใช้ที่มีบทบาทนี้</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($role->users as $user)
                                            <div class="flex items-center space-x-3 p-3 bg-white rounded border">
                                                <div class="flex-shrink-0">
                                                    @if($user->avatar)
                                                        <img class="h-8 w-8 rounded-full object-cover" 
                                                             src="{{ Storage::url($user->avatar) }}" 
                                                             alt="{{ $user->name }}">
                                                    @else
                                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-gray-600">
                                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                                    <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                                </div>
                                                <a href="{{ route('admin.users.show', $user) }}" 
                                                   class="text-indigo-600 hover:text-indigo-800">
                                                    <i class="fas fa-external-link-alt text-xs"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Submit Buttons --}}
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('admin.roles.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                ยกเลิก
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save mr-2"></i>อัปเดตบทบาท
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Select all permissions functionality
        document.getElementById('select-all-permissions').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.permission-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Update select all when individual checkboxes change
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.permission-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.permission-checkbox:checked');
                const selectAllCheckbox = document.getElementById('select-all-permissions');
                
                selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
            });
        });

        // Initialize select all state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const allCheckboxes = document.querySelectorAll('.permission-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.permission-checkbox:checked');
            const selectAllCheckbox = document.getElementById('select-all-permissions');
            
            selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
        });
    </script>
</x-app-layout>