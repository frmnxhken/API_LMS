<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{
    public function store($id_subject_class, $id_post, Request $request)
    {
        $user = Auth::user();
        $validation = Validator::make($request->all(), [
            "files" => "required",
            "files.*" => "file|max:10240"
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()]);
        }

        try {
            $submission = Submission::where("post_id", $id_post)->where("student_id", $user->student->id)->firstOrFail();;
            $submission->status = "done";
            $submission->save();
            $files = $request->file('files');

            if ($files) {
                if (!is_array($files)) {
                    $files = [$files];
                }
                $this->uploadSubmissionFile($submission, $files);
            }

            return response()->json([
                "message" => "Submission berhasil diupload",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage()
            ], 500);
        }
    }

    protected function uploadSubmissionFile(Submission $submission,  $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('posts', 'public');
            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
