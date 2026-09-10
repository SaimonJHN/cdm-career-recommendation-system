<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('students')
            ->select(['id', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(500, function ($students) {
                $rows = $students->map(fn ($student) => [
                    'student_id' => $student->id,
                    'action' => 'registered',
                    'auth_method' => 'existing_account',
                    'ip_address' => null,
                    'user_agent' => null,
                    'created_at' => $student->created_at ?? $student->updated_at ?? now(),
                    'updated_at' => $student->created_at ?? $student->updated_at ?? now(),
                ])->all();

                if ($rows !== []) {
                    DB::table('student_activity_logs')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        DB::table('student_activity_logs')
            ->where('action', 'registered')
            ->where('auth_method', 'existing_account')
            ->delete();
    }
};
