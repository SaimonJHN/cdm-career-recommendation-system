<?php
namespace Tests\Unit;
use App\Http\Controllers\AuthController;
use PHPUnit\Framework\TestCase;
class GoogleAccountTest extends TestCase
{
    public function test_verified_google_identity_requires_email_and_subject(): void
    {
        $method = new \ReflectionMethod(AuthController::class, 'isVerifiedGoogleAccount');
        foreach (['student@example.com', 'student@student.pnm.edu.ph'] as $email) {
            $this->assertTrue($method->invoke(new AuthController(), ['email' => $email, 'sub' => 'google-subject', 'email_verified' => true]));
        }
        foreach ([['email' => 'student@example.com', 'sub' => 'subject', 'email_verified' => false], ['email' => 'student@example.com', 'email_verified' => true], ['email' => 'not-an-email', 'sub' => 'subject', 'email_verified' => true]] as $claims) {
            $this->assertFalse($method->invoke(new AuthController(), $claims));
        }
    }
}
