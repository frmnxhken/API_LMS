<?php

namespace App\Services;

use App\Models\ClassSubject;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\StudentEnrollment;

class GradeService
{
    public function generateForStudent(
        int $studentId,
        int $schoolClassId
    ): void {

        $classSubjectIds = ClassSubject::where(
            'school_class_id',
            $schoolClassId
        )->pluck('id');

        if ($classSubjectIds->isEmpty()) {
            return;
        }

        $grades = [];

        foreach ($classSubjectIds as $classSubjectId) {
            $grades[] = [
                'student_id' => $studentId,
                'class_subject_id' => $classSubjectId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Grade::upsert(
            $grades,
            ['student_id', 'class_subject_id']
        );
    }

    public function generateForClassSubject(
        int $classSubjectId
    ): void {

        $classSubject = ClassSubject::findOrFail($classSubjectId);

        $grades = StudentEnrollment::where(
            'school_class_id',
            $classSubject->school_class_id
        )
            ->pluck('student_id')
            ->map(fn($studentId) => [
                'student_id' => $studentId,
                'class_subject_id' => $classSubjectId,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if (empty($grades)) {
            return;
        }

        Grade::upsert(
            $grades,
            ['student_id', 'class_subject_id']
        );
    }
}
