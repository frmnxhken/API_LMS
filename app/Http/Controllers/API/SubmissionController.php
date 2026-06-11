<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmissionRequest;
use App\Models\Post;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    public function __construct(
        protected SubmissionService $service,
    ) {}

    public function store($id_subject_class, $id_post, SubmissionRequest $request)
    {
        $post = Post::findOrFail($id_post);

        if ($post->due && today()->gt($post->due)) {
            return response()->json([
                'message' => 'Deadline telah berakhir'
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $id_post) {
                $user = Auth::user();

                $submission = Submission::firstOrCreate([
                    "post_id" => $id_post,
                    "student_id" => $user->student->id,
                ]);

                $submission->update(["status" => "done"]);
                $files = $request->file('files');
                $this->service->upload($submission, $files);
            });

            return response()->json(["message" => "uploaded"], 200);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
}
