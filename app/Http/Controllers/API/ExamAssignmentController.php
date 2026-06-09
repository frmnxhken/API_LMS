<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamAssignmentStoreRequest;
use App\Http\Requests\ExamAssignmentUpdateRequest;
use App\Models\ClassSubject;
use App\Models\ExamAssignment;
use App\Services\ExamAssignmentService;
use App\Services\ExamAttemptService;
use Illuminate\Support\Facades\DB;

class ExamAssignmentController extends Controller
{
    public function __construct(
        protected ExamAssignmentService $examService,
        protected ExamAttemptService $attemptService,
    ) {}

    public function index($id_class_subject)
    {
        $assignments = ExamAssignment::where("class_subject_id", $id_class_subject)
            ->with("exam")->latest()->get();

        $classSubject = ClassSubject::with("subject")->findOrFail($id_class_subject);

        return response()->json([
            "meta" => [
                "subject" => $classSubject->subject,
            ],
            "data" => $assignments,
        ]);
    }

    public function store(ExamAssignmentStoreRequest $request, $id_class_subject)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $id_class_subject) {
            $assignment = $this->examService->create($data, $id_class_subject);
            $this->attemptService->generate($assignment->id, $id_class_subject);
        });

        return response()->json(["message" => "created"], 201);
    }

    public function show($id_class_subject, $exam)
    {
        return ExamAssignment::where("class_subject_id", $id_class_subject)
            ->with(["exam.subject"])
            ->findOrFail($exam);
    }

    public function update(ExamAssignmentUpdateRequest $request, $id_class_subject, $exam)
    {
        $assignment = ExamAssignment::where("class_subject_id", $id_class_subject)
            ->findOrFail($exam);

        $assignment->update($request->validated());
        return response()->json(["message" => "updated"]);
    }

    public function destroy($id_class_subject, $exam)
    {
        DB::transaction(function () use ($id_class_subject, $exam) {
            $assignment = ExamAssignment::with(["exam"])->findOrFail($exam);
            $examType = $assignment->exam->type;

            $this->attemptService->rollbackFromAttempts($assignment, $examType);
            $assignment->delete();
        });

        return response()->json(["message" => "deleted"]);
    }
}
