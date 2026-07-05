<?php

namespace App\Services;

use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentService
{
    public function remove(StudentEnrollment $enrollment)
    {
        DB::transaction(function () use ($enrollment) {
            $classSubjectIds = ClassSubject::where(
                'school_class_id',
                $enrollment->school_class_id
            )->pluck('id');

            Grade::where('student_id', $enrollment->student_id)
                ->whereIn('class_subject_id', $classSubjectIds)
                ->delete();

            $enrollment->delete();
        });
    }
}
