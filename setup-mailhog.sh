#!/bin/bash
# 📧 Setup Mailhog Email Verification

echo "📧 กำลังตั้งค่า Mailhog Email Verification..."

# 1. ตรวจสอบ Mailhog Container
echo "🐳 ตรวจสอบ Mailhog Container..."
if docker-compose ps | grep -q mailhog; then
    echo "✅ Mailhog container กำลังทำงาน"
else
    echo "⚠️  Mailhog container ไม่ทำงาน - กำลังเริ่มต้น..."
    docker-compose up -d mailhog
fi

# 2. อัปเดต .env สำหรับ Mailhog
echo "⚙️  อัปเดต .env Configuration..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Remove existing mail configuration
sed -i '/^MAIL_/d' .env

# Add Mailhog configuration
cat >> .env << 'EOF'

# Mail Configuration (Mailhog)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
EOF

echo "✅ .env อัปเดตแล้ว"

# 3. สร้าง Email Verification Customization
echo "📝 สร้าง Custom Email Verification..."

# สร้าง Custom Verify Email Notification
mkdir -p app/Notifications
cat > app/Notifications/CustomVerifyEmail.php << 'EOF'
<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmailBase implements ShouldQueue
{
    use Queueable;

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('ยืนยันที่อยู่อีเมลของคุณ - ' . config('app.name'))
            ->greeting('สวัสดี ' . $notifiable->name . '!')
            ->line('กรุณาคลิกปุ่มด้านล่างเพื่อยืนยันที่อยู่อีเมลของคุณ')
            ->action('ยืนยันอีเมล', $verificationUrl)
            ->line('หากคุณไม่ได้สร้างบัญชีนี้ ไม่จำเป็นต้องดำเนินการใดๆ')
            ->line('ลิงก์นี้จะหมดอายุใน 60 นาที')
            ->salutation('ขอบคุณ,<br>' . config('app.name'));
    }
}
EOF

# 4. อัปเดต User Model ให้ใช้ Custom Notification
echo "👤 อัปเดต User Model..."
# Add sendEmailVerificationNotification method to User model
cat >> app/Models/User.php << 'EOF'

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }
EOF

# 5. สร้าง Email Verification Controller สำหรับ Custom Actions
echo "🎛️  สร้าง Email Verification Controller..."
cat > app/Http/Controllers/EmailVerificationController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function show(Request $request): View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard'))
                    : view('auth.verify-email');
    }

    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
EOF

# 6. สร้าง Custom Email Verification View
echo "🎨 สร้าง Custom Email Verification View..."
mkdir -p resources/views/auth
cat > resources/views/auth/verify-email.blade.php << 'EOF'
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
EOF

# 7. อัปเดต Routes สำหรับ Email Verification
echo "🛣️  อัปเดต Email Verification Routes..."
if ! grep -q "verification.send" routes/web.php; then
    cat >> routes/web.php << 'EOF'

// Custom Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [App\Http\Controllers\EmailVerificationController::class, 'show'])
        ->name('verification.notice');
    
    Route::post('/email/verification-notification', [App\Http\Controllers\EmailVerificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});
EOF
fi

# 8. สร้าง Command สำหรับทดสอบ Email
echo "🧪 สร้าง Test Email Command..."
cat > app/Console/Commands/TestEmailCommand.php << 'EOF'
<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'test:email {--user=1}';
    protected $description = 'Send a test email verification to user';

    public function handle()
    {
        $userId = $this->option('user');
        $user = User::find($userId);

        if (!$user) {
            $this->error('User not found');
            return;
        }

        $this->info("Sending test email to: {$user->email}");

        try {
            $user->notify(new CustomVerifyEmail);
            $this->info('✅ Email sent successfully!');
            $this->info('🌐 Check Mailhog at: http://localhost:8025');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
        }
    }
}
EOF

# 9. สร้าง Middleware สำหรับบังคับยืนยัน Email
echo "🛡️  สร้าง Email Verification Middleware..."
cat > app/Http/Middleware/EnsureEmailIsVerified.php << 'EOF'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
            ! $request->user()->hasVerifiedEmail())) {
            return $request->expectsJson()
                    ? abort(409, 'Your email address is not verified.')
                    : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
EOF

# 10. สร้าง Dashboard Badge สำหรับ Email Status
echo "🎨 อัปเดต Dashboard สำหรับ Email Status..."
# เพิ่ม email verification status ใน dashboard
sed -i 's/ยืนยันแล้ว/✅ ยืนยันแล้ว/g' resources/views/dashboard.blade.php
sed -i 's/รอยืนยัน/⚠️ รอยืนยัน/g' resources/views/dashboard.blade.php

# 11. Clear configs และ restart services
echo "🧹 Clear configurations..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Register the test command
php artisan optimize

echo ""
echo "🎉 Mailhog Email Verification Setup เสร็จสิ้น!"
echo ""
echo "🌐 Access Points:"
echo "┌─────────────────────────────────────────┐"
echo "│ 📧 Mailhog Web UI: http://localhost:8025 │"
echo "│ 🏠 Application:   http://localhost:8080  │"
echo "└─────────────────────────────────────────┘"
echo ""
echo "🧪 การทดสอบ:"
echo "1. สร้าง User ใหม่ หรือใช้ User ที่มีอยู่"
echo "2. ส่ง Test Email:"
echo "   php artisan test:email --user=1"
echo "3. เช็คอีเมลใน Mailhog: http://localhost:8025"
echo ""
echo "⚙️ การใช้งาน:"
echo "• User ที่ยังไม่ยืนยันอีเมลจะถูกนำไปหน้า verification"
echo "• คลิก 'ส่งอีเมลยืนยันอีกครั้ง' เพื่อส่งอีเมลใหม่"
echo "• ตรวจสอบอีเมลใน Mailhog แล้วคลิกลิงก์ยืนยัน"
echo ""
echo "🔧 Debug Commands:"
echo "• php artisan test:email --user=1"
echo "• php artisan queue:work (ถ้าใช้ Queue)"
echo "• docker-compose logs mailhog"
echo ""
echo "✨ Features:"
echo "• ✅ Custom Thai Email Templates"
echo "• ✅ Mailhog Integration"
echo "• ✅ Email Verification UI"
echo "• ✅ Test Commands"
echo "• ✅ Queue Support"
echo ""
echo "🚀 พร้อมทดสอบการยืนยันอีเมลแล้ว!"