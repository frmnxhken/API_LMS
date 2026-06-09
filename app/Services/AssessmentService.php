<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    public function updateScore(int $submissionId, int $classSubjectId, float $newScore): void
    {
        DB::transaction(function () use ($submissionId, $classSubjectId, $newScore) {
            $submission = Submission::findOrFail($submissionId);
            $oldScore = $submission->score ?? 0;

            $grade = Grade::where('student_id', $submission->student_id)
                ->where('class_subject_id', $classSubjectId)
                ->firstOrFail();

            $grade->update([
                'assignment_total_score' =>
                $grade->assignment_total_score - $oldScore + $newScore
            ]);

            $submission->update([
                'score' => $newScore,
                'status' => 'graded'
            ]);
        });
    }
}
