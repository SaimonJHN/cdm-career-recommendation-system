<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function dashboard(Request $request)
    {
        $student = $request->user();
        return response()->json([
            'success' => true,
            'student' => $student->makeHidden(['exam_score', 'recommended_program']),
            'student_number' => $student->student_number,
            'admission_year' => $student->admission_year,
            'exam_taken' => $student->exam_taken,
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $student = $request->user();

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'phone' => 'sometimes|nullable|string|max:30',
            'date_of_birth' => 'sometimes|nullable|date',
            'profile_picture' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $oldProfilePicture = null;
        if ($request->hasFile('profile_picture')) {
            $oldProfilePicture = $student->profile_picture;
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'The profile picture could not be stored.',
                ], 500);
            }

            $validated['profile_picture'] = $path;
        }

        $student->update($validated);

        if (!empty($oldProfilePicture) && $oldProfilePicture !== $student->profile_picture && !filter_var($oldProfilePicture, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($oldProfilePicture);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'student' => $student->fresh()->makeHidden(['exam_score', 'recommended_program']),
        ], 200);
    }

    public function getStudentNumber(Request $request)
    {
        $student = $request->user();

        return response()->json([
            'success' => true,
            'student_number' => $student->student_number,
            'admission_year' => $student->admission_year,
            'format' => 'CDM-YYYY-XXXXX',
            'description' => 'Your unique applicant identification number for Colegio de Montalban',
        ], 200);
    }
}
