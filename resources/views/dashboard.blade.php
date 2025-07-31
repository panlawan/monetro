{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">สวัสดี, {{ $user->name }}!</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            เข้าสู่ระบบล่าสุด: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'ไม่เคยเข้าสู่ระบบ' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- User Info -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3">ข้อมูลผู้ใช้</h4>
                            <div class="space-y-2 text-sm">
                                <div><strong>อีเมล:</strong> {{ $user->email }}</div>
                                <div><strong>โทรศัพท์:</strong> {{ $user->phone ?? 'ไม่ระบุ' }}</div>
                                <div><strong>สถานะ:</strong> 
                                    <span class="px-2 py-1 rounded text-xs {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Roles -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium mb-3">บทบาท (Roles)</h4>
                            <div class="space-y-2">
                                @forelse($user->roles as $role)
                                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                        {{ $role->display_name }}
                                    </span>
                                @empty
                                    <p class="text-sm text-gray-500">ไม่มีบทบาท</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6">
                        <h4 class="font-medium mb-3">การดำเนินการ</h4>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('profile.edit') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                แก้ไขโปรไฟล์
                            </a>
                            
                            @if($user->hasRole('admin') || $user->hasRole('super_admin'))
                                <a href="{{ route('admin.dashboard') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Admin Panel
                                </a>
                            @endif

                            @if($user->hasRole('moderator'))
                                <a href="{{ route('moderator.dashboard') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    Moderator Panel
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>