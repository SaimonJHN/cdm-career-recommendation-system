<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB};
use App\Models\{PortalNotification, AdminActivityLog};
class OperationsController extends Controller
{
    public function health()
    {
        DB::select('SELECT 1');
        $heartbeat = Cache::get('portal:heartbeat');
        $backups = glob(storage_path('app/private/backups/*.sha256')) ?: [];
        usort($backups, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        return response()->json(['database' => 'OK', 'scheduler_ok' => $heartbeat && now()->diffInSeconds(\Carbon\Carbon::parse($heartbeat), true) < 180,
            'scheduler_last_run' => $heartbeat, 'latest_backup' => $backups ? date(DATE_ATOM, filemtime($backups[0])) : null,
            'pending_email_count' => PortalNotification::whereNull('emailed_at')->count(),
            'failed_emails' => PortalNotification::whereNull('emailed_at')->where('email_attempts', '>=', 5)->latest('id')->limit(50)->get(['id', 'student_id', 'message', 'email_attempts', 'created_at'])]);
    }
    public function retry(Request $request, int $id)
    {
        $item = PortalNotification::whereKey($id)->whereNull('emailed_at')->firstOrFail();
        $item->update(['email_attempts' => 0, 'email_retry_at' => now()]);
        AdminActivityLog::create(['admin_id' => $request->user()->id, 'action' => 'notification.retry', 'subject_type' => PortalNotification::class, 'subject_id' => $id, 'details' => ['reason' => 'Operator requested retry'], 'ip_address' => $request->ip()]);
        return response()->json(['message' => 'Notification queued for the next scheduler run.']);
    }
}
