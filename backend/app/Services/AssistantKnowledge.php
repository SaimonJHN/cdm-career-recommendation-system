<?php
namespace App\Services;

use App\Models\Course;

class AssistantKnowledge
{
    // Approved public help, shared by spoken AI answers and the browser's built-in help.
    public static function topics(): array
    {
        $minutes = ExamSessionService::TIME_LIMIT_MINUTES;
        $hours = $minutes / 60;
        $attempts = ExamSessionService::MAX_ATTEMPTS;
        $programs = Course::where('is_active', true)->orderBy('display_order')->get(['code', 'name'])->map(fn ($course) => "{$course->code}: {$course->name}")->implode('; ');
        return [
            ['words' => ['how does the system work', 'how it works', 'paano gumagana'], 'answer' => 'Create and verify your account, complete your profile, and take the entrance assessment. Open My Recommendation from navigation or the dashboard, rate your interests, and generate guidance. Each match links to its subjects and career paths. Your official result status appears after the Registrar publishes it. Recommendations are guidance, not a final admission decision.'],
            ['words' => ['programs are available', 'available programs', 'anong course'], 'answer' => $programs ? "Current active programs: {$programs}. Open Programs for details." : 'Open Programs for current course information.'],
            ['words' => ['retake', 'attempt', 'failed', 'bagsak'], 'answer' => "Students have a maximum of {$attempts} exam attempts. One final retake becomes available only after the Registrar publishes a failed first result. After a failed second attempt, contact the Registrar for guidance; another exam attempt is not available."],
            ['words' => ['timer', 'time limit', 'how long', 'autosave', 'saved answers', 'resume exam'], 'answer' => "Each new attempt, including a retake, randomly selects 20 active questions in each of five topics and shuffles topic and question order. Resuming preserves the assigned order. The exam lasts {$minutes} minutes ({$hours} hours), displayed as hours, minutes, and seconds. Answers save to your account while connected. The deadline continues if you leave; at expiry, the server submits the answers it received. Reconnect before time runs out to save offline changes."],
            ['words' => ['forgot password', 'password'], 'answer' => 'Active, verified email-and-password accounts can use Forgot password on the sign-in page. The reset link expires after 30 minutes. Google-created accounts should use Sign in with Google. Never share passwords or OTP codes.'],
            ['words' => ['notification', 'application progress', 'refresh status'], 'answer' => 'Your application progress is below Program Overview in the dashboard sidebar. It shows status, attempts, history, and notifications when results are published or corrected. Refresh status checks for updates. Result emails require working mail delivery and scheduled maintenance.'],
            ['words' => ['passed', 'registrar', 'official result', 'exam result'], 'answer' => 'Your official result status appears after the Registrar publishes it. If your result is PASSED, please visit the CDM Registrar’s Office as soon as possible for guidance on the next steps in your admission process. Open Status to see your own published result.'],
            ['words' => ['otp', 'verification code'], 'answer' => 'The OTP verifies your email during registration or sign-in. Enter it only on the official verification screen and never give it to another person.'],
        ];
    }
}
