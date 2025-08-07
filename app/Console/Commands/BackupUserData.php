<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupUserData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:backup {user_id : The ID of the user to backup}';

    /**
     * The console command description.
     */
    protected $description = 'Create a backup of all user data before deletion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $this->info("Creating backup for user: {$user->email}");

        try {
            $backupData = [
                'user' => $user->toArray(),
                'iot_devices' => $user->iotDevices()->with(['sockets.readings', 'sockets.alerts'])->get()->toArray(),
                'questions' => $user->questions()->get()->toArray(),
                'profiles' => $user->profiles()->with('readings')->get()->toArray(),
                'notifications' => DB::table('notifications')
                    ->where('notifiable_id', $user->id)
                    ->where('notifiable_type', User::class)
                    ->get()
                    ->toArray(),
                'backup_created_at' => Carbon::now()->toISOString(),
                'backup_reason' => 'Pre-deletion backup'
            ];

            $fileName = "user-backup-{$user->id}-" . Carbon::now()->format('Y-m-d-H-i-s') . '.json';
            $filePath = "backups/users/{$fileName}";

            Storage::disk('local')->put($filePath, json_encode($backupData, JSON_PRETTY_PRINT));

            $this->info("Backup created successfully: {$filePath}");
            $this->info("Backup file size: " . Storage::disk('local')->size($filePath) . " bytes");

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to create backup: " . $e->getMessage());
            return 1;
        }
    }
}
