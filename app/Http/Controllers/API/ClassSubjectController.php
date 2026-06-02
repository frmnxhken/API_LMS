<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\ClassSubjectResource;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\UserResource;
use App\Models\ClassSubject;
use App\Models\Post;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassSubjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === "teacher") {
            $classSubjects = ClassSubject::with('subject', 'teacher.user', 'schoolClass')->where('teacher_id', $user->teacher->id)->get();
        } elseif ($user->role === 'student') {
            $classSubjects = ClassSubject::with('subject', 'teacher.user', 'schoolClass')->whereHas('schoolClass.enrollments', function ($q) use ($user) {
                $q->where('student_id', $user->student->id);
            })->get();
        }
        // return $classSubjects;
        return ClassSubjectResource::collection($classSubjects);
    }

    public function activity($id_class_subject)
    {
        $posts = ClassSubject::with([
            "posts" => function ($query) {
                $query->orderBy("id", "DESC");
            },
            "subject"
        ])
            ->where("id", $id_class_subject)
            ->get();
        return response()->json(ActivityResource::collection($posts));
    }

    public function memberClass($id_class_subject)
    {
        $classSubject = ClassSubject::with('teacher.user')->where('id', $id_class_subject)->first();
        $teacher = $classSubject->teacher;
        $students = Student::whereHas('enrollments.schoolClass.classSubjects', function ($q) use ($id_class_subject) {
            $q->where('class_subjects.id', $id_class_subject);
        })->with('user')->get();

        return response()->json(["teacher" => new TeacherResource($teacher), "students" => UserResource::collection($students)]);
    }
}
