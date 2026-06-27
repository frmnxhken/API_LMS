<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Models\PostFile;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function __construct(private PostService $service) {}

    public function detailPost($id_class_subject, $id_post)
    {
        $user = Auth::user();
        if ($user->role === "student") {
            $post = Post::where("class_subject_id", $id_class_subject)
                ->with(["post_files", "submissions" => function ($query) use ($user) {
                    $query->where("student_id", $user->student->id);
                }, "submissions.submission_files"])
                ->find($id_post);
        } elseif ($user->role === "teacher") {
            $post = Post::where("class_subject_id", $id_class_subject)
                ->with("post_files")
                ->find($id_post);
        }

        if (!$post) {
            return response()->json(["message" => "Postingan tidak ditemukan di kelas ini"], 404);
        }

        return response()->json(["data" => $post]);
    }

    public function store($id_class_subject, PostRequest $request)
    {
        $type = $request->segment(4);

        $post = $this->service->create(
            $id_class_subject,
            $type,
            $request->validated()
        );

        return response()->json([
            'message' => 'success',
            'data' => $post
        ], 201);
    }

    public function update($id_class_subject, $id_post, PostRequest $request)
    {
        $type = $request->segment(4);

        $post = $this->service->update(
            $id_class_subject,
            $id_post,
            $type,
            $request->validated()
        );

        return response()->json([
            'message' => 'success',
            'data' => $post
        ]);
    }

    public function delete($id_class_subject, $id_post, Request $request)
    {
        $this->service->delete(
            $id_class_subject,
            $id_post
        );

        return response()->json([
            'message' => 'success'
        ]);
    }

    public function deletePostFile($id_file)
    {
        $file = PostFile::findOrFail($id_file);

        if (Storage::exists($file->file_path)) {
            Storage::delete($file->file_path);
        }

        $file->delete();

        return response()->json(['message' => 'File berhasil dihapus']);
    }
}
