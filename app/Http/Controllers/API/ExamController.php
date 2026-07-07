<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamRequest;
use App\Http\Resources\ExamEditResource;
use App\Models\Exam;
use App\Services\ExamAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{

    public function __construct(
        protected ExamAttemptService $attemptService,
    ) {}

    public function index($id_class_subject)
    {
        $exams = Exam::where("class_subject_id", $id_class_subject)->latest()->get();

        return response()->json([
            "data" => $exams,
        ]);
    }

    public function store(ExamRequest $request, $id_class_subject)
    {
        $exam = DB::transaction(function () use ($request, $id_class_subject) {
            $exam = Exam::create([
                'teacher_id' => Auth::user()->teacher->id,
                'class_subject_id' => $id_class_subject,
                'title' => $request['title'],
                'start_time' => $request['start_time'],
                'end_time' => $request['end_time'],
                'duration' => $request['duration'],
                'type' => $request['type'],
                'is_random_questions' => $request['is_random_questions'],
            ]);

            $exam->questions()->attach($request['questions']);
            $this->attemptService->generate($exam->id, $id_class_subject);

            return $exam;
        });

        return response()->json([
            'message' => 'Ujian berhasil dibuat.',
            'data' => $exam->load('questions'),
        ], 201);
    }

    public function show($id_class_subject, Exam $exam)
    {
        $exam->load(['questions.options', 'questions.questionBank']);
        return response()->json([
            ...$exam->toArray(),
            'questions' => ExamEditResource::collection($exam->questions),
        ]);
    }

    public function update(Request $request, $id_class_subject, Exam $exam)
    {
        $exam = DB::transaction(function () use ($request, $exam, $id_class_subject) {
            $exam->update([
                'class_subject_id' => $id_class_subject,
                'title' => $request['title'],
                'start_time' => $request['start_time'],
                'end_time' => $request['end_time'],
                'duration' => $request['duration'],
                'type' => $request['type'],
                'is_random_questions' => $request['is_random_questions'],
            ]);

            $exam->questions()->sync($request['questions']);

            return $exam;
        });

        return response()->json([
            'message' => 'Ujian berhasil diperbarui.',
            'data' => $exam->load('questions'),
        ]);
    }

    public function destroy($id_class_subject, Exam $exam)
    {
        DB::transaction(function () use ($id_class_subject, $exam) {
            $examType = $exam->type;
            $this->attemptService->rollbackFromAttempts($exam, $examType);
            $exam->delete();
        });

        return response()->json(["message" => "deleted"]);
    }
}
