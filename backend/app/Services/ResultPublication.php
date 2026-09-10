<?php
namespace App\Services;
use App\Models\{ExamResult, PortalNotification};
class ResultPublication
{
    public static function notify(ExamResult $result): void
    {
        PortalNotification::create(['student_id' => $result->student_id,
            'message' => $result->official_outcome === 'RETAKE' ? 'Your official result is available. You have one final retake available.' : 'Your official examination result has been published or updated. Open Results to view it.',
            'link' => '/results']);
    }
}
