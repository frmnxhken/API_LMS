<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $teacher = Teacher::where("user_id", $user->id)->first();
        $data = Exam::with("subject")->where("teacher_id", $teacher->id)->get();
        return response()->json([
            "data" => $data
        ]);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "title" => "required|unique:exams,title",
            "subject_id" => "required",
            "duration" => "required",
            "type" => "required",
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()], 422);
        }

        try {
            $user = Auth::user();
            $teacher = Teacher::where("user_id", $user->id)->first();
            Exam::create([
                "teacher_id" => $teacher->id,
                "subject_id" => $request->subject_id,
                "title" => $request->title,
                "duration" => $request->duration,
                "type" => $request->type,
            ]);
            return response()->json(["message" => "Success"], 201);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


    public function show($id)
    {
        $exam = Exam::with('subject')->find($id);
        return response()->json([
            "message" => "Success fetching exam detail",
            "data" => $exam
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $exam = Exam::find($id);

        $validation = Validator::make($request->all(), [
            "title" => "required|unique:exams,title," . $id,
            "subject_id" => "required",
            "duration" => "required",
            "type" => "required",
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()], 422);
        }

        try {
            $user = Auth::user();
            $teacher = Teacher::where("user_id", $user->id)->first();

            if ($exam->teacher_id !== $teacher->id) {
                return response()->json(["message" => "Anda tidak memiliki akses untuk mengubah ujian ini"], 403);
            }

            $exam->update([
                "subject_id" => $request->subject_id,
                "title" => $request->title,
                "duration" => $request->duration,
                "type" => $request->type,
            ]);

            return response()->json([
                "message" => "Success updated",
                "data" => $exam
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Failed to update exam",
                "error" => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $exam = Exam::find($id);

        try {
            $user = Auth::user();
            $teacher = Teacher::where("user_id", $user->id)->first();

            if ($exam->teacher_id !== $teacher->id) {
                return response()->json(["message" => "Anda tidak memiliki akses untuk menghapus ujian ini"], 403);
            }

            $exam->delete();
            return response()->json(["message" => "Success deleted"], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Failed to delete exam",
                "error" => $th->getMessage()
            ], 500);
        }
    }
}
