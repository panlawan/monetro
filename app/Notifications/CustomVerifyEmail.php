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
