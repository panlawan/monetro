{{-- resources/views/admin/users/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('เพิ่มผู้ใช้ใหม่') }}
            </h2>
            <a href="{{ route('admin.users.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i>กลับ
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Left Column --}}
                            <div class="space-y-6">
                                {{-- Avatar Upload --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">รูปโปรไฟล์</label>
                                    <div class="flex items-center space-x-6">
                                        <div class="shrink-0">
                                            <img id="avatar-preview" class="h-20 w-20 object-cover rounded-full border-2 border-gray-300" 
                                                 src="https://via.placeholder.com/80x80.png?text=Avatar" alt="Preview">
                                        </div>
                                        <label class="block">
                                            <span class="sr-only">Choose profile photo</span>
                                            <input type="file" name="avatar" id="avatar-input" accept="image/*"
                                                   class="block w-full text-sm text-slate-500
                                                          file:mr-4 file:py-2 file:px-4
                                                          file:rounded-full file:border-0
                                                          file:text-sm file:font-semibold
                                                          file:bg-violet-50 file:text-violet-700
                                                          hover:file:bg-violet-100">
                                        </label>
                                    </div>
                                    @error('avatar')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Name --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">ชื่อ-นามสกุล *</label>
                                    <input type="text" name="name" id="name" 
                                           value="{{ old('name') }}" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">อีเมล *</label>
                                    <input type="email" name="email" id="email" 
                                           value="{{ old('email') }}" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">เบอร์โทรศัพท์</label>
                                    <input type="text" name="phone" id="phone" 
                                           value="{{ old('phone') }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="space-y-6">
                                {{-- Password --}}
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน *</label>
                                    <div class="relative">
                                        <input type="password" name="password" id="password" required
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10">
                                        <button type="button" onclick="togglePassword('password')" 
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <i class="fas fa-eye text-gray-400" id="password-icon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">ยืนยันรหัสผ่าน *</label>
                                    <div class="relative">
                                        <input type="password" name="password_confirmation" id="password_confirmation" required
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
                                               {{ old('is_active', true) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">เปิดใช้งานบัญชี</span>
                                    </label>
                                </div>

                                {{-- Roles --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">บทบาท</label>
                                    <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-md p-3">
                                        @foreach($roles as $role)
                                            <label class="flex items-start space-x-3">
                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                                       {{ collect(old('roles'))->contains($role->id) ? 'checked' : '' }}
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
                                <i class="fas fa-save mr-2"></i>บันทึกผู้ใช้
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

        // Generate password button
        function generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            
            document.getElementById('password').value = password;
            document.getElementById('password_confirmation').value = password;
        }
    </script>
</x-app-layout>