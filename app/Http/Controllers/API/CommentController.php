<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function index($id_class_subject, $id_post)
    {
        $comments = Comment::with("user")->where("post_id", $id_post)->get();
        return response()->json($comments);
    }

    public function store($id_class_subject, $id_post, Request $request)
    {
        $validation = Validator::make($request->all(), [
            "message" => "required|min:1"
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()]);
        }

        $user = Auth::user();

        Comment::create([
            "user_id" => $user->id,
            "post_id" => $id_post,
            "message" => $request->message
        ]);

        return response()->json(["message" => "Success"]);
    }
}
