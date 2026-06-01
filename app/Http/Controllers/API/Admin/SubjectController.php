<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::get();
        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "name" => "required|unique:subjects,name"
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()]);
        }

        try {
            Subject::create($request->all());
            return response()->json(["message" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            "name" => "required|unique:subjects,name," . $id
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()]);
        }

        try {
            Subject::find($id)->update($request->all());
            return response()->json(["message" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            Subject::find($id)->delete();
            return response()->json(["message" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()]);
        }
    }
}
