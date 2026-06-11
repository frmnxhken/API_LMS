<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamRequest;
use App\Models\Exam;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    private function teacher()
    {
        $teacher = Teacher::where("user_id", Auth::user()->id)->first();
        return $teacher;
    }

    public function index()
    {
        $data = Exam::with('subject')->where('teacher_id', $this->teacher()->id)->get();
        return response()->json(['data' => $data]);
    }

    public function store(ExamRequest $request)
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacher()->id,
            ...$request->validated(),
        ]);

        return response()->json(["message" => "success"], 201);
    }

    public function show($id)
    {
        $exam = Exam::with('subject')->find($id);
        return response()->json([
            "message" => "Success fetching exam detail",
            "data" => $exam
        ], 200);
    }

    public function update(ExamRequest $request, $id)
    {
        $exam = Exam::find($id);
        $this->authorizeExam($exam);

        $exam->update($request->validated());
        return response()->json([
            "message" => "success",
            "data" => $exam
        ], 200);
    }

    public function destroy($id)
    {
        $exam = Exam::find($id);
        $this->authorizeExam($exam);

        $exam->delete();
        return response()->json(["message" => "success"]);
    }

    private function authorizeExam($exam)
    {
        if ($exam->teacher_id !== $this->teacher()->id) {
            abort(403, 'Anda tidak memiliki akses untuk ini');
        }
    }
}
