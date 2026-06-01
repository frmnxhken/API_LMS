<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::get();
        return response()->json($classes);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "level" => "required",
            "major" => "required"
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()]);
        }

        try {
            SchoolClass::create($request->all());
            return response()->json(["message" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            "level" => "required",
            "major" => "required"
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()]);
        }

        try {
            SchoolClass::find($id)->update($request->all());
            return response()->json(["message" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            SchoolClass::find($id)->delete();
            return response()->json(["message" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()]);
        }
    }
}
