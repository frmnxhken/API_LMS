<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionBankRequest;
use App\Models\ClassSubject;
use App\Models\QuestionBank;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class QuestionBankController extends Controller
{
    public function index()
    {
        $data = QuestionBank::with('subject')->where('teacher_id', $this->teacher()->id)->get();
        return response()->json(['data' => $data]);
    }

    public function list($id_class_subject)
    {
        $classSubject = ClassSubject::findOrFail($id_class_subject);
        $data = QuestionBank::with(['subject', 'teacher.user'])
            ->where('subject_id', $classSubject->subject_id)->get();

        return response()->json($data);
    }

    public function show($id)
    {
        $data = QuestionBank::with('subject')->find($id)->first();
        return response()->json(['data' => $data]);
    }

    public function store(QuestionBankRequest $request, QuestionBank $questionBank)
    {
        $questionBank->create([
            'teacher_id' => $this->teacher()->id,
            ...$request->validated()
        ]);
        return response()->json(["message" => "success"]);
    }

    public function update(QuestionBankRequest $request, QuestionBank $questionBank)
    {
        $questionBank->update($request->validated());
        return response()->json(["message" => "success"]);
    }

    public function destroy(QuestionBank $questionBank)
    {
        $questionBank->delete();
        return response()->json(["message" => "success"]);
    }

    private function teacher()
    {
        $teacher = Teacher::where("user_id", Auth::user()->id)->first();
        return $teacher;
    }
}
