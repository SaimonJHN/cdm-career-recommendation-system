<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{Artisan, DB};
use App\Models\{Student, ExamQuestion};
use App\Services\ProgramMatcher;
class PrepareLoadTest extends Command
{
    protected $signature = 'portal:prepare-load {--students=1000}';
    protected $description = 'Create an isolated SQLite load-test database and private disposable tokens; never uses the application database.';
    public function handle(): int
    {
        if (app()->environment('production')) { $this->error('Run this only in a development environment.'); return self::FAILURE; }
        $count = filter_var($this->option('students'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);
        if (!$count) { $this->error('Choose 1–1000 students.'); return self::FAILURE; }
        $directory = storage_path('app/private/load-test');
        if (is_dir($directory)) { $this->error('The isolated load-test directory already exists. Use its database or archive it before preparing another run.'); return self::FAILURE; }
        mkdir($directory, 0700, true); touch($directory.'/load-test.sqlite');
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $directory.'/load-test.sqlite', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
        // Synthetic questions exist in this isolated database only.
        ExamQuestion::query()->delete();
        foreach (ProgramMatcher::INTEREST_CATEGORIES as $categoryIndex => $category) for ($i = 1; $i <= 20; $i++) {
            ExamQuestion::create(['question_number' => $categoryIndex * 20 + $i, 'category' => $category, 'question_text' => 'Synthetic load-test question '.$i, 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'A', 'is_active' => true]);
        }
        $tokens = [];
        for ($i = 1; $i <= $count; $i++) {
            $student = Student::create(['first_name' => 'Load', 'last_name' => 'Test '.$i, 'email' => "load{$i}@example.invalid", 'student_number' => 'LOAD-'.$i, 'admission_year' => 'TEST', 'account_status' => 'active']);
            $tokens[] = $student->createToken('disposable-load-test')->plainTextToken;
        }
        file_put_contents($directory.'/tokens.json', json_encode($tokens));
        $this->info("Prepared {$count} disposable students. Database and tokens: {$directory}");
        return self::SUCCESS;
    }
}
