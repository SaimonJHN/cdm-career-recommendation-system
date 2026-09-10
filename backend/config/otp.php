<?php

return [
    'expires_minutes' => (int) env('OTP_EXPIRES_MINUTES', 10),
    'resend_seconds' => (int) env('OTP_RESEND_SECONDS', 60),
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'max_resends' => (int) env('OTP_MAX_RESENDS', 5),
    'max_sends_per_hour' => (int) env('OTP_MAX_SENDS_PER_HOUR', 5),
    'trusted_device_days' => (int) env('OTP_TRUSTED_DEVICE_DAYS', 30),
];
