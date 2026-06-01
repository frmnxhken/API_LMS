<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Experiment extends Controller
{
    public function getClass()
    {
        $classes = SchoolClass::get();
        return response()->json($classes);
    }

    public function getStudent()
    {
        $activeYear = AcademicYear::where('is_active', 1)->first();

        $students = StudentEnrollment::where('academic_year_id')->get();
        // $students = StudentEnrollment::with([
        // 'student.user',
        // 'schoolClass'
        // ])
        // ->where('academic_year_id', $activeYear->id)
        // ->get();
        // 
        return response()->json($students);
    }

    public function storeStudent(Request $request)
    {
        DB::beginTransaction();

        try {
            // 1. Create user
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => bcrypt($request->password),
                'photo' => '',
                'role' => 'student'
            ]);

            // 2. Create student
            $student = Student::create([
                'user_id' => $user->id,
                'nis' => $request->nis
            ]);

            // 3. Get active academic year
            $activeYear = AcademicYear::where('is_active', true)->first();

            // 4. Enrollment
            StudentEnrollment::create([
                'student_id' => $student->id,
                'school_class_id' => $request->school_class_id,
                'academic_year_id' => $activeYear->id
            ]);

            DB::commit();

            return response()->json(['message' => 'Student created']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getAcademic()
    {
        return AcademicYear::all();
    }

    public function storeAcademic(Request $request)
    {
        AcademicYear::create([
            'name' => $request->name,
            'is_active' => false
        ]);

        return response()->json(['message' => 'Academic year created']);
    }

    public function updateAcademic($id)
    {
        DB::beginTransaction();

        try {
            AcademicYear::query()->update(['is_active' => false]);

            AcademicYear::where('id', $id)->update([
                'is_active' => true
            ]);

            DB::commit();

            return response()->json(['message' => 'Academic year activated']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getTeaching()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        $data = ClassSubject::with([
            'schoolClass',
            'subject',
            'teacher.user'
        ])
            ->where('academic_year_id', $activeYear->id)
            ->get();

        return response()->json($data);
    }

    public function storeTeaching(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        ClassSubject::create([
            'school_class_id' => $request->school_class_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'academic_year_id' => $activeYear->id
        ]);

        return response()->json(['message' => 'Teaching created']);
    }
}
