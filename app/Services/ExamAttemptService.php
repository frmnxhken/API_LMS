<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\Student;

class ExamAttemptService
{
    public function generate(int $assignmentId, int $classSubjectId): void
    {
        $students = Student::whereHas(
            'enrollments.schoolClass.classSubjects',
            function ($q) use ($classSubjectId) {
                $q->where('class_subjects.id', $classSubjectId);
            }
        )->get();

        $data = $students->map(function ($student) use ($assignmentId) {
            return [
                'exam_assignment_id' => $assignmentId,
                'student_id' => $student->id,
                'score' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        ExamAttempt::insert($data);
    }

    public function rollbackFromAttempts($assignment, $examType): void
    {
        $attempts = ExamAttempt::where('exam_assignment_id', $assignment->id)->get();

        foreach ($attempts as $attempt) {
            $grade = Grade::firstOrCreate([
                'student_id' => $attempt->student_id,
                'class_subject_id' => $assignment->class_subject_id,
            ]);

            $score = $attempt->score ?? 0;

            switch ($examType) {
                case 'uas':
                    $grade->update(['uas_score' => 0]);
                    break;
                case 'uts':
                    $grade->update(['uts_score' => 0]);
                    break;
                case 'harian':
                    $grade->decrement('daily_total_score', $score);
                    break;
            }
        }
    }
}
