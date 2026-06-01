<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssesmentController extends Controller
{
    public function index($id_class_subject, $id_post)
    {
        $submissions = Submission::where('post_id', $id_post)
            ->whereHas('post.classSubject', function ($q) use ($id_class_subject) {
                $q->where('id', $id_class_subject);
            })->with('student.user')
            ->get();
        return $submissions;
    }

    public function show($id_class_subject, $id_submission)
    {
        $submission = Submission::with(['submission_files', 'student.user'])
            ->where('id', $id_submission)
            ->whereHas('post', function ($q) use ($id_class_subject) {
                $q->where('class_subject_id', $id_class_subject);
            })
            ->firstOrFail();

        return response()->json($submission);
    }

    public function update($id_class_subject, $id_submission, Request $request)
    {
        $validation = Validator::make($request->all(), [
            "score" => "required|min:0|max:100"
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()]);
        }

        try {
            Submission::find($id_submission)->update([
                "score" => $request->score,
                "status" => "graded"
            ]);

            return response()->json(["message" => "Success"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()]);
        }
    }
}
