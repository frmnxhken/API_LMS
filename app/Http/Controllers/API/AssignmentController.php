<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Models\ClassSubject;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        $posts = Post::with([
            'classSubject.subject',
            'classSubject.schoolClass',
        ])->where('type', 'assignment')
            ->whereHas('classSubject.schoolClass.enrollments', function ($query) use ($user) {
                $query->where('student_id', $user->student->id);
            })->latest()->get();

        return AssignmentResource::collection($posts);
    }

    public function assignmentClass($id_class_subject)
    {
        $posts = ClassSubject::with([
            "posts" => function ($query) {
                $query->latest()->where("type", "assignment");
            },
        ])->where("id", $id_class_subject)->get();
        return response()->json($posts);
    }
}
