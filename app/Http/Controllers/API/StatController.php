<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;

class StatController extends Controller
{
    public function statAdmin()
    {
        $teacherTotal = Teacher::count();
        $studentTotal = StudentEnrollment::count();
        $subjectTotal = Subject::count();
        $schoolClassTotal = SchoolClass::count();

        return response()->json([
            "teacher_total" => $teacherTotal,
            "student_total" => $studentTotal,
            "subject_total" => $subjectTotal,
            "class_total" => $schoolClassTotal,
        ]);
    }
}
