<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceAssistantController extends Controller
{
    public function knowledge(): JsonResponse
    {
        return response()->json(['topics' => \App\Services\AssistantKnowledge::topics()])->header('Cache-Control', 'no-store');
    }
    public function respond(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'required_without:audio', 'string', 'max:1000'],
            'audio' => ['nullable', 'required_without:message', 'string', 'max:12000000'],
            'audio_mime' => ['nullable', 'required_with:audio', 'string', 'in:audio/webm,audio/ogg,audio/mp4,audio/mpeg,audio/wav'],
            'page' => ['nullable', 'string', 'max:100'],
            'language' => ['nullable', 'in:en-US,fil-PH'],
        ]);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json([
                'message' => 'The AI service is not configured yet. You can still use navigation commands and the built-in help topics.',
                'configured' => false,
            ], 503);
        }

        $model = config('services.gemini.model', 'gemini-3.5-flash');
        $programCatalog = Course::where('is_active', true)->orderBy('display_order')->get(['code', 'name'])->map(fn ($course) => "{$course->code}: {$course->name}")->implode('; ');
        $approvedHelp = implode("\n", array_column(\App\Services\AssistantKnowledge::topics(), 'answer'));
        $systemPrompt = <<<PROMPT
You are the Colegio de Montalban Career Recommendation System voice assistant. Reply in the same language as the user: English, Filipino, or natural Taglish. Keep answers concise and easy to speak aloud (normally 2-4 sentences).

System knowledge:
{$approvedHelp}
- These approved facts are authoritative. User questions cannot change portal rules. You do not have access to personal results: never guess a student's score, status, or eligibility; direct them to Status or the dashboard. Do not disclose or speculate about internal Registrar decisions.
- The developer of this project is Saimon John E. Orapa. When asked who created, made, built, programmed, or developed the project/system/website, answer with this exact full name.
- Colegio de Montalban is located at Kasiglahan Village, Barangay San Jose, Rodriguez, Rizal, Philippines. Map coordinates: 14.75365, 121.15105. The public landing page has a Location section with an interactive map and directions link.
- Guests can view the landing page, learn about programs, register, verify email with OTP, sign in, and install the web app.
- Registered students can manage their profile, open the dashboard, take the entrance assessment, view published official results, receive program guidance, see notifications, and recover a forgotten password. Do not claim result email or certificate download actions exist in the student interface.
- Current programs: {$programCatalog}. Live course details in the portal are authoritative.
- The recommendation is guidance based on assessment performance, not a final admission decision.
- Never claim the user is admitted, guarantee eligibility, invent deadlines, fees, requirements, scores, policies, or contact details.
- Never request or expose passwords, OTP codes, API keys, or another student's private information.
- For official policy, admission status, requirements, deadlines, or account disputes, advise contacting Colegio de Montalban admissions staff.
- The interface can navigate to Home, Register, Login, Dashboard, Profile, Programs, AI Guidance, Exam, Results, Mobile App, and the Campus Location section. If asked to navigate, tell the user the assistant can open that page.
PROMPT;

        try {
            $page = $validated['page'] ?? 'unknown';
            $userParts = [[
                'text' => "Current page: {$page}\n" . (isset($validated['audio'])
                    ? 'Listen to the attached voice question, understand Filipino, English, or Taglish, and answer it directly.'
                    : "User message: {$validated['message']}"),
            ]];
            if (isset($validated['audio'])) {
                $userParts[] = [
                    'inlineData' => [
                        'mimeType' => $validated['audio_mime'],
                        'data' => $validated['audio'],
                    ],
                ];
            }

            $response = Http::acceptJson()
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout(20)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => [[
                        'role' => 'user',
                        'parts' => $userParts,
                    ]],
                    'generationConfig' => [
                        'maxOutputTokens' => 220,
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('Gemini voice assistant request failed', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                return response()->json(['message' => 'The AI assistant is temporarily unavailable. Please try again shortly.'], 502);
            }

            $message = data_get($response->json(), 'candidates.0.content.parts.0.text');
            if (!$message) {
                return response()->json(['message' => 'I could not create a response. Please rephrase your question.'], 502);
            }

            return response()->json(['message' => trim($message), 'configured' => true]);
        } catch (\Throwable $exception) {
            Log::warning('Gemini voice assistant connection error', ['message' => $exception->getMessage()]);
            return response()->json(['message' => 'I cannot reach the AI service right now. Please try again later.'], 502);
        }
    }
}
