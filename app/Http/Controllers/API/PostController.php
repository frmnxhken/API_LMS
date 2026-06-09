<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Post;
use App\Models\PostFile;
use App\Models\Student;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
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

    public function store($id_class_subject, Request $request)
    {
        $type = $request->segment(4);
        $validateData = array_merge($request->all(), ["type" => $type]);
        $validator = Validator::make($validateData, [
            "title" => "required",
            "content" => "required",
            "due" => "required_if:type,assignment|date",
            "files" => "nullable|array",
            "files.*" => "file|max:20480",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $due = ($type === "material") ? null : $request->due;
            $post = Post::create([
                "title" => $request->title,
                "content" => $request->content,
                "type" => $type,
                "due" => $due,
                "class_subject_id" => $id_class_subject
            ]);

            if ($request->hasFile('files')) {
                $this->uploadPostFiles($post, $request->file('files'));
            }

            if ($type === "assignment") {
                $this->generateSubmission($post, $id_class_subject);
            }

            return response()->json([
                "data" => $post
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage()
            ], 500);
        }
    }

    public function update($id_class_subject, $id_post, Request $request)
    {
        $type = $request->segment(4);
        $validateData = array_merge($request->all(), ["type" => $type]);
        $validator = Validator::make($validateData, [
            "title" => "required",
            "content" => "required",
            "due" => "required_if:type,assignment|date",
            "files" => "nullable|array",
            "files.*" => "file|max:20480",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $post = Post::where("class_subject_id", $id_class_subject)
                ->where("id", $id_post)
                ->firstOrFail();

            $due = ($type === "material") ? null : $request->due;

            $post->update([
                "title" => $request->title,
                "content" => $request->content,
                "due" => $due,
            ]);

            if ($request->hasFile('files')) {
                $this->uploadPostFiles($post, $request->file('files'));
            }

            return response()->json([
                "data" => $post
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage()
            ], 500);
        }
    }

    public function delete($id_class_subject, $id_post, Request $request)
    {
        $post = Post::where("class_subject_id", $id_class_subject)
            ->with("post_files")
            ->find($id_post);

        if (!$post) {
            return response()->json([
                "error" => "Post not found"
            ], 404);
        }

        DB::transaction(function () use ($post, $id_class_subject) {

            foreach ($post->post_files as $file) {
                if ($file->path && Storage::exists($file->path)) {
                    Storage::delete($file->path);
                }
            }

            if ($post->type === 'assignment') {

                foreach ($post->submissions as $submission) {

                    if ($submission->score === null) {
                        continue;
                    }

                    Grade::where(
                        'student_id',
                        $submission->student_id
                    )
                        ->where(
                            'class_subject_id',
                            $id_class_subject
                        )
                        ->decrement(
                            'assignment_total_score',
                            $submission->score
                        );
                }
            }

            $post->post_files()->delete();

            $post->submissions()->delete();

            $post->delete();
        });

        return response()->json([
            'message' => 'Success'
        ]);
    }

    protected function generateSubmission(Post $post, $id_class_subject)
    {
        $students = Student::whereHas('enrollments.schoolClass.classSubjects', function ($q) use ($id_class_subject) {
            $q->where('class_subjects.id', $id_class_subject);
        })->get();

        foreach ($students as $student) {
            Submission::create([
                "post_id" => $post->id,
                "student_id" => $student->id,
                "status" => "pending",
                "score" => 0,
            ]);
        }
    }

    protected function uploadPostFiles(Post $post, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('posts', 'public');
            PostFile::create([
                'post_id' => $post->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
            ]);
        }
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
