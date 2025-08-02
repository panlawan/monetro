{{-- resources/views/admin/users/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('แก้ไขผู้ใช้: ' . $user->name) }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.users.show', $user) }}" 
                   class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-eye mr-2"></i>ดูข้อมูล
                </a>
                <a href="{{ route('admin.users.index') }}" 
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
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Left Column --}}
                            <div class="space-y-6">
                                {{-- Avatar Upload --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">รูปโปรไฟล์</label>
                                    <div class="flex items-center space-x-6">
                                        <div class="shrink-0">
                                            <img id="avatar-preview" class="h-20 w-20 object-cover rounded-full border-2 border-gray-300" 
                                                 src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://via.placeholder.com/80x80.png?text=Avatar' }}" 
                                                 alt="Preview">
                                        </div>
                                        <div>
                                            <label class="block mb-2">
                                                <span class="sr-only">Choose profile photo</span>
                                                <input type="file" name="avatar" id="avatar-input" accept="image/*"
                                                       class="block w-full text-sm text-slate-500
                                                              file:mr-4 file:py-2 file:px-4
                                                              file:rounded-full file:border-0
                                                              file:text-sm file:font-semibold
                                                              file:bg-violet-50 file:text-violet-700
                                                              hover:file:bg-violet-100">
                                            </label>
                                            @if($user->avatar)
                                                <button type="button" onclick="removeAvatar()" 
                                                        class="text-red-600 text-sm hover:text-red-800">
                                                    <i class="fas fa-trash mr-1"></i>ลบรูปภาพ
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    @error('avatar')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Name --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">ชื่อ-นามสกุล *</label>
                                    <input type="text" name="name" id="name" 
                                           value="{{ old('name', $user->name) }}" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">อีเมล *</label>
                                    <input type="email" name="email" id="email" 
                                           value="{{ old('email', $user->email) }}" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">เบอร์โทรศัพท์</label>
                                    <input type="text" name="phone" id="phone" 
                                           value="{{ old('phone', $user->phone) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Account Info --}}
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">ข้อมูลบัญชี</h4>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <div><strong>สร้างเมื่อ:</strong> {{ $user->created_at->format('d/m/Y H:i:s') }}</div>
                                        <div><strong>อัปเดตล่าสุด:</strong> {{ $user->updated_at->format('d/m/Y H:i:s') }}</div>
                                        <div><strong>ยืนยันอีเมล:</strong> 
                                            @if($user->email_verified_at)
                                                <span class="text-green-600">✓ ยืนยันแล้ว ({{ $user->email_verified_at->format('d/m/Y') }})</span>
                                            @else
                                                <span class="text-red-600">✗ ยังไม่ยืนยัน</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="space-y-6">
                                {{-- Password --}}
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่านใหม่ (เว้นว่างหากไม่ต้องการเปลี่ยน)</label>
                                    <div class="relative">
                                        <input type="password" name="password" id="password"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10">
                                        <button type="button" onclick="togglePassword('password')" 
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <i class="fas fa-eye text-gray-400" id="password-icon"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" onclick="generatePassword()" 
                                                class="text-sm text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-key mr-1"></i>สร้างรหัสผ่านใหม่
                                        </button>
                                    </div>
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">ยืนยันรหัสผ่านใหม่</label>
                                    <div class="relative">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10">
                                        <button type="button" onclick="togglePassword('password_confirmation')" 
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <i class="fas fa-eye text-gray-400" id="password_confirmation-icon"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" 
                                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">เปิดใช้งานบัญชี</span>
                                    </label>
                                </div>

                                {{-- Roles --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">บทบาท</label>
                                    <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-md p-3">
                                        @foreach($roles as $role)
                                            @php
                                                $isChecked = old('roles') 
                                                    ? collect(old('roles'))->contains($role->id)
                                                    : $user->roles->contains($role->id);
                                            @endphp
                                            <label class="flex items-start space-x-3">
                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                                       {{ $isChecked ? 'checked' : '' }}
                                                       class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $role->display_name }}</div>
                                                    @if($role->description)
                                                        <div class="text-xs text-gray-500">{{ $role->description }}</div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('roles')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Current Roles Display --}}
                                <div class="bg-blue-50 p-4 rounded-md">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">บทบาทปัจจุบัน</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($role->name === 'super_admin') bg-red-100 text-red-800
                                                @elseif($role->name === 'admin') bg-purple-100 text-purple-800
                                                @elseif($role->name === 'moderator') bg-yellow-100 text-yellow-800
                                                @else bg-green-100 text-green-800
                                                @endif">
                                                {{ $role->display_name }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400 italic text-sm">ไม่มีบทบาท</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('admin.users.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                ยกเลิก
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save mr-2"></i>อัปเดตผู้ใช้
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Avatar preview
        document.getElementById('avatar-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Remove avatar
        function removeAvatar() {
            if (confirm('คุณต้องการลบรูปโปรไฟล์ใช่หรือไม่?')) {
                document.getElementById('avatar-preview').src = 'https://via.placeholder.com/80x80.png?text=Avatar';
                document.getElementById('avatar-input').value = '';
                
                // Add hidden field to indicate avatar removal
                let removeField = document.querySelector('input[name="remove_avatar"]');
                if (!removeField) {
                    removeField = document.createElement('input');
                    removeField.type = 'hidden';
                    removeField.name = 'remove_avatar';
                    removeField.value = '1';
                    document.querySelector('form').appendChild(removeField);
                }
            }
        }

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Generate password
        function generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            
            document.getElementById('password').value = password;
            document.getElementById('password_confirmation').value = password;
            
            // Show password temporarily
            document.getElementById('password').type = 'text';
            document.getElementById('password_confirmation').type = 'text';
            document.getElementById('password-icon').classList.remove('fa-eye');
            document.getElementById('password-icon').classList.add('fa-eye-slash');
            document.getElementById('password_confirmation-icon').classList.remove('fa-eye');
            document.getElementById('password_confirmation-icon').classList.add('fa-eye-slash');
            
            alert('รหัสผ่านใหม่ถูกสร้างแล้ว: ' + password);
        }
    </script>
</x-app-layout>