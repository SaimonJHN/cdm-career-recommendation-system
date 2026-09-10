<?php

namespace Tests\Unit;

use App\Services\ProgramMatcher;
use App\Services\ProgramRecommendationAI;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

class ProgramRecommendationTest extends TestCase
{
    private function courses(): array
    {
        return [
            ['id' => 1, 'code' => 'IT', 'name' => 'Computing', 'recommendation_profile' => ['Technical Aptitude' => 3, 'Science' => 1]],
            ['id' => 2, 'code' => 'SCI', 'name' => 'Science', 'recommendation_profile' => ['Science' => 3, 'Technical Aptitude' => 1]],
        ];
    }

    public function test_normalizes_categories_and_combines_interest_with_exam_evidence(): void
    {
        $result = (new ProgramMatcher)->match(['Digital Literacy' => 4, 'Science' => 1], ['Digital Literacy' => 4, 'Science' => 4], ['Digital Literacy' => 5, 'Science' => 1], $this->courses());
        $this->assertSame('IT', $result['ranked_programs'][0]['course_code']);
        $this->assertSame(81.25, $result['ranked_programs'][0]['exam_match']);
        $this->assertSame(75.0, $result['ranked_programs'][0]['interest_match']);
        $this->assertSame(80.0, $result['ranked_programs'][0]['score']);
    }

    public function test_all_zero_missing_or_impossible_evidence_cannot_rank_programs(): void
    {
        foreach ([[['Science' => 0], ['Science' => 1]], [['Science' => 1], []], [['Science' => 2], ['Science' => 1]]] as [$scores, $maximums]) {
            $result = (new ProgramMatcher)->match($scores, $maximums, [], $this->courses());
            $this->assertSame('insufficient_evidence', $result['status']);
            $this->assertSame([], $result['ranked_programs']);
        }
    }

    public function test_ties_are_explicit_and_exam_only_does_not_invent_interests(): void
    {
        $result = (new ProgramMatcher)->match(['Digital Literacy' => 1, 'Science' => 1], ['Digital Literacy' => 1, 'Science' => 1], [], $this->courses());
        $this->assertSame(['IT', 'SCI'], $result['tied_top_codes']);
        $this->assertSame(100.0, $result['ranked_programs'][0]['score']);
        $this->assertNull($result['ranked_programs'][0]['interest_match']);
    }

    public function test_a_program_with_unmeasured_categories_is_excluded(): void
    {
        $result = (new ProgramMatcher)->match(['Science' => 1], ['Science' => 1], [], $this->courses());
        $this->assertSame('insufficient_evidence', $result['status']);
    }

    private function bootAI(): void
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        restore_error_handler();
        restore_exception_handler();
        config(['services.gemini.api_key' => 'test-only', 'services.gemini.model' => 'test-model']);
        Http::preventStrayRequests();
    }

    public function test_ai_explanations_are_validated_and_request_excludes_identity(): void
    {
        $this->bootAI();
        Http::fake(['*' => Http::response(['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => json_encode(['programs' => [['course_code' => 'IT', 'explanation' => 'Explore computing based on your assessment.', 'next_step' => 'Try a programming exercise.']]])]]]]]])]);
        $result = (new ProgramRecommendationAI)->explain(['ranked_programs' => [['course_code' => 'IT']]], [], []);
        $this->assertArrayHasKey('IT', $result);
        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'test-only') && isset($request['generationConfig']['responseJsonSchema']) && !str_contains($request->body(), 'student_id'));
    }

    public function test_unknown_ai_program_is_rejected(): void
    {
        $this->bootAI();
        Http::fake(['*' => Http::response(['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => '{"programs":[{"course_code":"INVENTED","explanation":"x","next_step":"y"}]}']]]]]])]);
        $this->expectException(\RuntimeException::class);
        (new ProgramRecommendationAI)->explain(['ranked_programs' => [['course_code' => 'IT']]], [], []);
    }

    public function test_incomplete_ai_response_is_rejected(): void
    {
        $this->bootAI();
        Http::fake(['*' => Http::response(['candidates' => [['finishReason' => 'MAX_TOKENS']]])]);
        $this->expectException(\RuntimeException::class);
        (new ProgramRecommendationAI)->explain(['ranked_programs' => [['course_code' => 'IT']]], [], []);
    }
}
