<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index($id_class_subject, $id_post)
    {
        $comments = Comment::with("user")->where("post_id", $id_post)->get();
        return response()->json($comments);
    }

    public function store($id_class_subject, $id_post, CommentRequest $commentRequest)
    {
        $user = Auth::user();
        Comment::create([
            "user_id" => $user->id,
            "post_id" => $id_post,
            "message" => $commentRequest->message
        ]);

        return response()->json(["message" => "Success"]);
    }
}
