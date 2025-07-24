<?php
// app/Console/Commands/CreateNetWorthSnapshots.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\NetWorthSnapshot;

class CreateNetWorthSnapshots extends Command
{
    protected $signature = 'finance:net-worth-snapshots {--date=}';
    protected $description = 'Create net worth snapshots for all users';

    public function handle()
    {
        $date = $this->option('date') ?? now()->format('Y-m-d');
        
        $users = User::whereHas('accounts')->get();
        
        $this->info("Creating net worth snapshots for {$users->count()} users on {$date}");
        
        if ($users->count() === 0) {
            $this->warn('No users with accounts found.');
            return;
        }
        
        $progressBar = $this->output->createProgressBar($users->count());
        
        foreach ($users as $user) {
            try {
                NetWorthSnapshot::createSnapshot($user->id, $date);
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("Error creating snapshot for user {$user->id}: " . $e->getMessage());
            }
        }
        
        $progressBar->finish();
        $this->newLine();
        $this->info('Net worth snapshots created successfully!');
    }
}
