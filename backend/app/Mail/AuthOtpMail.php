<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AuthOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $purpose,
        public int $expiresMinutes
    ) {
    }

    public function build(): self
    {
        $subject = $this->purpose === 'registration'
            ? 'Verify your CDM account'
            : 'Your CDM login verification code';

        return $this->subject($subject)
            ->view('emails.auth-otp');
    }
}
