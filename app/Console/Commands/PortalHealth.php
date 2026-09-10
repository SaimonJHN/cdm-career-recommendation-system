<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Cache};
use App\Models\PortalNotification;
class PortalHealth extends Command
{
    protected $signature = 'portal:health';
    protected $description = 'Check database, writable storage, notification outbox and scheduler heartbeat.';
    public function handle(): int
    {
        try {
            DB::select('SELECT 1');
            $heartbeat = Cache::get('portal:heartbeat');
            $checks = ['database' => true, 'storage' => is_writable(storage_path()),
                'scheduler' => $heartbeat && now()->diffInSeconds(\Carbon\Carbon::parse($heartbeat), true) < 180,
                'mail_outbox' => !PortalNotification::whereNull('emailed_at')->where(fn ($q) => $q->where('email_attempts', '>=', 5)->orWhere('created_at', '<', now()->subMinutes(30)))->exists()];
            foreach ($checks as $name => $ok) $this->line($name.': '.($ok ? 'OK' : 'NEEDS ATTENTION'));
            return in_array(false, $checks, true) ? self::FAILURE : self::SUCCESS;
        } catch (\Throwable $e) { $this->error('Health check failed: '.$e->getMessage()); return self::FAILURE; }
    }
}
