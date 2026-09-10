<?php

namespace App\Services;

use App\Models\Student;
use App\Models\TrustedDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrustedDeviceService
{
    public function issue(Student $student, Request $request): string
    {
        $plainTextToken = Str::random(80);
        $student->trustedDevices()->create([
            'token_hash' => hash('sha256', $plainTextToken),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'last_used_at' => now(),
            'expires_at' => now()->addDays($this->lifetimeDays()),
        ]);
        return $plainTextToken;
    }

    public function validFor(Student $student, ?string $plainTextToken): bool
    {
        if (!$plainTextToken) return false;

        $device = TrustedDevice::where('student_id', $student->id)
            ->where('token_hash', hash('sha256', $plainTextToken))
            ->first();

        if (!$device) return false;
        if ($device->expires_at->isPast()) {
            $device->delete();
            return false;
        }

        $device->forceFill(['last_used_at' => now()])->save();
        return true;
    }

    private function lifetimeDays(): int
    {
        return max(1, (int) config('otp.trusted_device_days', 30));
    }
}
