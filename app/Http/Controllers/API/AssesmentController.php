<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentRequest;
use App\Models\Submission;
use App\Services\AssessmentService;

class AssesmentController extends Controller
{
    public function __construct(protected AssessmentService $service) {}

    public function index($id_class_subject, $id_post)
    {
        $submissions = Submission::where("post_id", $id_post)
            ->whereHas("post.classSubject", function ($q) use ($id_class_subject) {
                $q->where("id", $id_class_subject);
            })->with("student.user")
            ->get();
        return $submissions;
    }

    public function show($id_class_subject, $id_submission)
    {
        $submission = Submission::with(["submission_files", "student.user"])
            ->where("id", $id_submission)
            ->whereHas("post", function ($q) use ($id_class_subject) {
                $q->where("class_subject_id", $id_class_subject);
            })
            ->firstOrFail();

        return response()->json($submission);
    }

    /*
         *
         * ketika guru update nilai maka: 
         * jika nilai saat ini 0 maka langsung tambahkan saja ke total nilai siswa
         * jika nilai ini ada sebelumnya maka:
         * ambil total score siswa lalu kurangi dengan nilai saat ini kemudian
         * tambahkan dengan nilai yang baru (total score - nilai lama + nilai baru)
         * 
         */
    public function update($id_class_subject, $id_submission, AssessmentRequest $request)
    {
        $this->service->updateScore(
            $id_submission,
            $id_class_subject,
            $request->score
        );

        return response()->json(["message" => "success"]);
    }
}
