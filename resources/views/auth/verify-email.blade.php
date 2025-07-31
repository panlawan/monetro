<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('ขอบคุณที่สมัครสมาชิก! ก่อนที่จะเริ่มใช้งาน กรุณายืนยันที่อยู่อีเมลของคุณโดยคลิกลิงก์ที่เราส่งให้คุณ หากคุณไม่ได้รับอีเมล เราสามารถส่งอีเมลใหม่ให้คุณได้') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('ลิงก์ยืนยันใหม่ได้ถูกส่งไปยังที่อยู่อีเมลที่คุณระบุไว้ตอนสมัครสมาชิกแล้ว') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('ส่งอีเมลยืนยันอีกครั้ง') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('ออกจากระบบ') }}
            </button>
        </form>
    </div>

    <!-- Mailhog Debug Info -->
    @if(config('app.debug'))
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        🧪 Development Mode - Mailhog
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>ตรวจสอบอีเมลที่ส่งได้ที่: <a href="http://localhost:8025" target="_blank" class="underline font-medium">Mailhog Web UI</a></p>
                        <p class="text-xs mt-1">อีเมลจะไม่ถูกส่งจริง แต่จะถูกจับไว้ใน Mailhog สำหรับการทดสอบ</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-guest-layout>
