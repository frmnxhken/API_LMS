<?php

namespace App\Services;

use App\Models\ClassSubject;
use App\Models\Post;

class AssignmentService
{
    public function getAllAssignments($user)
    {
        $posts = Post::with([
            'classSubject.subject',
            'classSubject.schoolClass',
        ])->where('type', 'assignment')
            ->whereHas('classSubject.schoolClass.enrollments', function ($query) use ($user) {
                $query->where('student_id', $user->student->id);
            })->latest()->get();

        return $posts;
    }

    public function getByClass($id)
    {
        $posts = ClassSubject::with([
            "posts" => function ($query) {
                $query->latest()->where("type", "assignment");
            },
        ])->where("id", $id)->get();

        return $posts;
    }
}
