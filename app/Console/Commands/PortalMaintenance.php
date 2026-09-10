<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\{ExamSession, Student, PortalNotification};
use App\Services\ExamSessionService;
use Illuminate\Support\Facades\{DB, Mail, Cache};
class PortalMaintenance extends Command
{
    protected $signature = 'portal:maintain {--no-mail : Finalize exams and prune expired records without sending mail}';
    protected $description = 'Finalize expired exams and process the persistent result notification outbox.';
    public function handle(ExamSessionService $service): int
    {
        ExamSession::whereNull('exam_result_id')->where('deadline', '<=', now())->chunkById(100, function ($sessions) use ($service) {
            foreach ($sessions as $session) $service->withLockedSession(Student::findOrFail($session->student_id), $session->id, fn ($s) => null);
        });
        if (!$this->option('no-mail')) {
            PortalNotification::whereNull('emailed_at')->where('email_attempts', '<', 5)
                ->where(fn ($q) => $q->whereNull('email_retry_at')->orWhere('email_retry_at', '<=', now()))->orderBy('id')->limit(100)->get()->each(function ($item) {
                    $lock = Cache::lock('notification:'.$item->id, 60);
                    if (!$lock->get()) return;
                    try {
                        $item->refresh(); if ($item->emailed_at) return;
                        $item->increment('email_attempts');
                        $email = Student::find($item->student_id)?->email;
                        if (!$email) return;
                        Mail::raw($item->message."\n".rtrim(config('services.portal_url'), '/').$item->link, fn ($m) => $m->to($email)->subject('CDM examination update'));
                        $item->update(['emailed_at' => now()]);
                    } catch (\Throwable $e) { report($e); $item->update(['email_retry_at' => now()->addMinutes(5 * $item->email_attempts)]); }
                    finally { $lock->release(); }
                });
        }
        DB::table('student_password_resets')->where('expires_at', '<', now())->delete();
        DB::table('result_import_previews')->where('expires_at', '<', now())->delete();
        Cache::put('portal:heartbeat', now()->toIso8601String(), now()->addDay());
        $this->info('Portal maintenance completed.'); return self::SUCCESS;
    }
}
