<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Console\Command;

class TestEmailCommand extends Command
{
    protected $signature = 'test:email {--user=1}';

    protected $description = 'Send a test email verification to user';

    public function handle()
    {
        $userId = $this->option('user');
        $user = User::find($userId);

        if (! $user) {
            $this->error('User not found');

            return Command::FAILURE;
        }

        $this->info("Sending test email to: {$user->email}");

        try {
            $user->notify(new CustomVerifyEmail);
            $this->info('✅ Email sent successfully!');
            $this->info('🌐 Check Mailhog at: http://localhost:8025');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
