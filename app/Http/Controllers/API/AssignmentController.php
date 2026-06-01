<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function index($id_class_subject)
    {
        $user = Auth::user();

        $posts = ClassSubject::with([
            "posts" => function ($query) {
                $query->orderBy("id", "DESC")->where("type", "assignment");
            },
        ])
            ->where("id", $id_class_subject)
            ->get();
        return response()->json($posts);
    }
}
