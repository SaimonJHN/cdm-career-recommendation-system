<?php
namespace App\Http\Controllers;
use App\Models\{Student, ExamResult, ExamSession, PortalNotification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Mail};
use Illuminate\Support\Str;

class PortalController extends Controller
{
    public function status(Request $request)
    {
        $student = $request->user();
        $results = ExamResult::where('student_id', $student->id)->orderBy('id')->get();
        $latest = $results->last();
        $session = ExamSession::where('student_id', $student->id)->whereNull('exam_result_id')->first();
        if ($session && now()->gte($session->deadline)) {
            $service = app(\App\Services\ExamSessionService::class);
            $service->withLockedSession($student, $session->id, fn ($s) => null);
            $results = ExamResult::where('student_id', $student->id)->orderBy('id')->get();
            $latest = $results->last(); $session = null;
        }
        return response()->json(['status' => $session ? 'IN_PROGRESS' : ($latest?->official_outcome ?? 'NOT_STARTED'),
            'attempts_used' => $results->count(), 'deadline' => $session?->deadline?->toIso8601String(),
            'timeline' => $results->map(fn ($r) => ['attempt' => $r->attempt_number, 'submitted_at' => $r->exam_date->toIso8601String(), 'status' => $r->official_outcome]),
            'notifications' => PortalNotification::where('student_id', $student->id)->latest('id')->limit(30)->get(['id', 'message', 'link', 'read_at', 'created_at'])]);
    }
    public function readNotification(Request $request, int $id)
    {
        PortalNotification::where('student_id', $request->user()->id)->whereKey($id)->firstOrFail()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }
    public function forgetPassword(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:191']);
        $email = strtolower(trim($data['email']));
        $student = Student::where('email', $email)->where('account_status', 'active')->first();
        if ($student && !$student->is_google_account && $student->email_verified_at) {
            $token = Str::random(64);
            DB::table('student_password_resets')->updateOrInsert(['email' => $email], ['token_hash' => hash('sha256', $token), 'expires_at' => now()->addMinutes(30)]);
            $url = rtrim(config('services.portal_url'), '/').'/reset-password?token='.urlencode($token).'&email='.urlencode($email);
            try { Mail::raw("Reset your CDM password within 30 minutes: {$url}\nIf you did not request this, ignore this email.", fn ($m) => $m->to($email)->subject('Reset your CDM password')); }
            catch (\Throwable $e) { report($e); }
        }
        return response()->json(['message' => 'If this is an eligible verified account, a password reset link will be sent. Google accounts should use Google sign-in.']);
    }
    public function resetPassword(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'token' => 'required|string|size:64', 'password' => 'required|string|min:8|max:128|confirmed']);
        return DB::transaction(function () use ($data) {
            $email = strtolower(trim($data['email']));
            $reset = DB::table('student_password_resets')->where('email', $email)->lockForUpdate()->first();
            abort_unless($reset && now()->lt($reset->expires_at) && hash_equals($reset->token_hash, hash('sha256', $data['token'])), 422, 'This password reset link is invalid or expired.');
            $student = Student::where('email', $email)->where('account_status', 'active')->lockForUpdate()->firstOrFail();
            abort_if($student->is_google_account, 422, 'Use Google sign-in for this account.');
            $student->update(['password' => Hash::make($data['password'])]);
            $student->tokens()->delete(); $student->trustedDevices()->delete();
            DB::table('auth_otp_challenges')->where('email', $email)->update(['consumed_at' => now()]);
            DB::table('student_password_resets')->where('email', $email)->delete();
            return response()->json(['message' => 'Password reset. Sign in again; all previous sessions have been signed out.']);
        });
    }
    public function revokeOthers(Request $request)
    {
        $student = $request->user();
        $student->tokens()->where('id', '!=', $student->currentAccessToken()->id)->delete();
        $student->trustedDevices()->delete();
        return response()->json(['message' => 'Other sessions signed out. Trusted devices will need verification on their next login.']);
    }
}
