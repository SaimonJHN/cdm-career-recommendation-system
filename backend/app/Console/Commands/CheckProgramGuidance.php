<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\ProgramMatcher;
use App\Services\ProgramRecommendationAI;
use Illuminate\Console\Command;

class CheckProgramGuidance extends Command
{
    protected $signature = 'ai:check-program-guidance';
    protected $description = 'Check Gemini program guidance using synthetic scores only; does not change student records';

    public function handle(ProgramMatcher $matcher, ProgramRecommendationAI $ai): int
    {
        $catalog = Course::where('is_active', true)->where('is_recommendable', true)->get()
            ->map(fn ($course) => $course->only(['id', 'code', 'name', 'description', 'subjects', 'career_paths', 'recommendation_profile']))->all();
        $maximums = array_fill_keys(ProgramMatcher::INTEREST_CATEGORIES, 4);
        $scores = array_fill_keys(ProgramMatcher::INTEREST_CATEGORIES, 2);
        $scores['Digital Literacy'] = 4;
        $interests = array_fill_keys(ProgramMatcher::INTEREST_CATEGORIES, 3);
        $interests['Digital Literacy'] = 5;
        $matches = $matcher->match($scores, $maximums, $interests, $catalog);
        if ($matches['status'] !== 'ready') { $this->error($matches['message']); return self::FAILURE; }
        try {
            $explanations = $ai->explain($matches, $interests, $catalog);
            $this->info('Gemini returned valid explanations for '.count($explanations).' programs using synthetic evidence.');
            $first = $matches['ranked_programs'][0]['course_code'];
            $this->line($first.': '.$explanations[$first]['explanation']);
            return self::SUCCESS;
        } catch (\Illuminate\Http\Client\RequestException $exception) {
            $this->error('Gemini returned HTTP '.$exception->response->status().'. Check the configured model, key, and quota.');
        } catch (\Throwable $exception) {
            $this->error('Guidance check failed ('.class_basename($exception).'). Check AI configuration and connectivity.');
        }
        return self::FAILURE;
    }
}
