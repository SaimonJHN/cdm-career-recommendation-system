<?php
namespace App\Http\Controllers;
use App\Models\{ExamResult, AdminActivityLog, Student};
use App\Services\ResultPublication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResultReviewController extends Controller
{
    public function approveOne(Request $request, ExamResult $examResult)
    {
        $data = $request->validate(['official_score' => 'required|integer|min:0|max:100', 'registrar_remarks' => 'nullable|string|max:2000', 'version' => 'required|string|size:64']);
        return DB::transaction(function () use ($request, $data, $examResult) {
            $result = ExamResult::whereKey($examResult->id)->lockForUpdate()->firstOrFail();
            abort_unless(!$result->registrar_pass && $result->official_status !== 'published' && hash_equals(self::version($result), $data['version']), 409, 'This result changed. Refresh before approving it.');
            $before = $result->getAttributes();
            $result->update(['official_score' => $data['official_score'], 'registrar_remarks' => $data['registrar_remarks'] ?? null, 'official_status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
            $this->audit($request, $result, 'result.approved', $before, 'Individual approval');
            return response()->json(['message' => 'Official result approved.']);
        });
    }
    public function publishOne(Request $request, ExamResult $examResult)
    {
        $data = $request->validate(['version' => 'required|string|size:64']);
        return DB::transaction(function () use ($request, $data, $examResult) {
            $result = ExamResult::whereKey($examResult->id)->lockForUpdate()->firstOrFail();
            abort_unless($result->official_status === 'approved' && $result->official_score !== null && hash_equals(self::version($result), $data['version']), 409, 'This result changed or is not approved. Refresh before publishing.');
            $before = $result->getAttributes();
            $result->update(['official_status' => 'published', 'published_by' => $request->user()->id, 'published_at' => now()]);
            $this->audit($request, $result, 'result.published', $before, 'Individual publication');
            ResultPublication::notify($result);
            return response()->json(['message' => 'Official result published.']);
        });
    }
    public static function version(ExamResult $result): string
    {
        return hash('sha256', json_encode($result->getRawOriginal()));
    }
    private function audit(Request $request, ExamResult $result, string $action, array $before, string $reason): void
    {
        AdminActivityLog::create(['admin_id' => $request->user()->id, 'action' => $action, 'subject_type' => ExamResult::class,
            'subject_id' => $result->id, 'details' => ['before' => $before, 'after' => $result->getAttributes(), 'reason' => $reason], 'ip_address' => $request->ip()]);
    }
    public function approveBatch(Request $request)
    {
        $data = $request->validate(['result_ids' => 'required|array|min:1|max:10000', 'result_ids.*' => 'integer|distinct']);
        return DB::transaction(function () use ($request, $data) {
            $results = ExamResult::whereIn('id', $data['result_ids'])->where('official_status', 'pending')->where('registrar_pass', false)->orderBy('id')->lockForUpdate()->get();
            abort_unless($results->count() === count($data['result_ids']), 409, 'Selection changed or includes non-pending results. Refresh and select pending results again.');
            foreach ($results as $result) {
                $before = $result->getAttributes();
                $result->update(['official_score' => $result->total_score, 'official_status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
                $this->audit($request, $result, 'result.bulk_approved', $before, 'Approved using system score');
            }
            return response()->json(['message' => $results->count().' results approved using system scores. Publish approved results to release them.']);
        });
    }
    public function correct(Request $request, ExamResult $examResult)
    {
        $data = $request->validate(['official_score' => 'required|integer|min:0|max:100', 'reason' => 'required|string|max:2000', 'version' => 'required|string|size:64']);
        return DB::transaction(function () use ($request, $data, $examResult) {
            Student::whereKey($examResult->student_id)->lockForUpdate()->firstOrFail();
            $result = ExamResult::whereKey($examResult->id)->lockForUpdate()->firstOrFail();
            abort_unless(hash_equals(self::version($result), $data['version']) && $result->official_status === 'published', 409, 'This result changed. Refresh before correcting it.');
            abort_if(ExamResult::where('student_id', $result->student_id)->where('id', '>', $result->id)->exists(), 422, 'Correct the latest attempt; prior attempts remain historical records.');
            abort_if(\App\Models\ExamSession::where('student_id', $result->student_id)->whereNull('exam_result_id')->exists(), 409, 'The student has an active retake. Resolve that attempt before correcting this result.');
            $before = $result->getAttributes();
            $result->update(['official_score' => $data['official_score'], 'registrar_pass' => false, 'registrar_pass_reason' => null, 'registrar_pass_by' => null, 'registrar_pass_at' => null,
                'approved_by' => $request->user()->id, 'approved_at' => now(), 'published_by' => $request->user()->id, 'published_at' => now()]);
            $this->audit($request, $result, 'result.corrected', $before, $data['reason']);
            ResultPublication::notify($result);
            return response()->json(['message' => 'Corrected result published. The previous decision is preserved in the audit log.']);
        });
    }
    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        try { $rows = app(AdminManagementController::class)->readResultImport($request->file('file')); }
        catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
        $header = array_map(fn ($h) => strtolower(trim((string) $h, "\xEF\xBB\xBF \t\r\n")), array_shift($rows) ?? []);
        abort_if(count(array_unique($header)) !== count($header) || array_diff(['applicant_number', 'result_id', 'result_version', 'score'], $header), 422, 'Use a freshly downloaded template with applicant_number, result_id, result_version and score columns.');
        abort_if(count($rows) > 1000, 422, 'Import up to 1,000 rows per batch.');
        $preview = []; $errors = []; $seen = [];
        foreach ($rows as $index => $values) {
            if (!array_filter($values, fn ($v) => trim((string) $v) !== '')) continue;
            $row = array_combine($header, array_slice(array_pad($values, count($header), null), 0, count($header)));
            $result = ExamResult::find($row['result_id']);
            $score = filter_var($row['score'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
            $valid = $result && !isset($seen[$result->id]) && $result->student->student_number === trim((string) $row['applicant_number'])
                && !$result->registrar_pass && $result->official_status !== 'published' && $score !== false
                && hash_equals(self::version($result), (string) $row['result_version'])
                && !ExamResult::where('student_id', $result->student_id)->where('id', '>', $result->id)->exists();
            if (!$valid) { $errors[] = 'Row '.($index + 2).': invalid, duplicate, published or stale result. Download a new template.'; continue; }
            $seen[$result->id] = true;
            $preview[] = ['result_id' => $result->id, 'applicant_number' => $row['applicant_number'], 'version' => self::version($result), 'old_score' => $result->official_score, 'score' => $score, 'remarks' => mb_substr(trim((string) ($row['remarks'] ?? '')), 0, 2000)];
        }
        $id = (string) Str::uuid();
        if (!$errors && $preview) DB::table('result_import_previews')->insert(['id' => $id, 'admin_id' => $request->user()->id, 'rows' => json_encode($preview), 'expires_at' => now()->addMinutes(15), 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['preview_id' => !$errors && $preview ? $id : null, 'rows' => $preview, 'errors' => $errors, 'message' => 'Review changes. Nothing has been imported yet.']);
    }
    public function commit(Request $request)
    {
        $data = $request->validate(['preview_id' => 'required|uuid']);
        return DB::transaction(function () use ($request, $data) {
            $preview = DB::table('result_import_previews')->where('id', $data['preview_id'])->where('admin_id', $request->user()->id)->lockForUpdate()->first();
            abort_unless($preview && now()->lt($preview->expires_at), 422, 'Import preview expired. Upload the file again.');
            foreach (json_decode($preview->rows, true) as $row) {
                $result = ExamResult::whereKey($row['result_id'])->lockForUpdate()->firstOrFail();
                abort_unless(hash_equals(self::version($result), $row['version']) && !ExamResult::where('student_id', $result->student_id)->where('id', '>', $result->id)->exists(), 409, 'A result changed after preview. Nothing was imported; preview again.');
                $before = $result->getAttributes();
                $result->update(['official_score' => $row['score'], 'registrar_remarks' => $row['remarks'], 'official_status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
                $this->audit($request, $result, 'result.imported', $before, 'Confirmed import preview');
            }
            DB::table('result_import_previews')->where('id', $preview->id)->delete();
            return response()->json(['message' => 'Import approved. Publish approved results to release them.']);
        });
    }
}
