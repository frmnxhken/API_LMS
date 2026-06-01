<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeachingAssignmentResource;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeachingAssignmentController extends Controller
{
    public function index()
    {
        $data = ClassSubject::with(["teacher.user", "schoolClass", "subject"])->get();
        return response()->json(TeachingAssignmentResource::collection($data));
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "school_class_id" => "required",
            "subject_id" => "required",
            "teacher_id" => "required"
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()]);
        }

        try {
            $activeYear = AcademicYear::where('is_active', 1)->firstOrFail();
            $request["academic_year_id"] = $activeYear->id;
            ClassSubject::create($request->all());
            return response()->json(["message" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()]);
        }
    }
}
