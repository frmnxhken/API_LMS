<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SchoolClassController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware("academicYear:draft", except: ["index", "list", "show"]),
        ];
    }

    public function index()
    {
        return response()->json(SchoolClass::get());
    }

    public function list()
    {
        $data = SchoolClass::withCount('enrollments')->get();
        return response()->json($data);
    }

    public function show(SchoolClass $schoolClass)
    {
        return response()->json($schoolClass);
    }

    public function store(SchoolClassRequest $request)
    {
        SchoolClass::create($request->validated());
        return response()->json(["message" => "success"], 201);
    }

    public function update(SchoolClassRequest $request, SchoolClass $schoolClass)
    {
        $schoolClass->update($request->validated());
        return response()->json(["message" => "success"], 201);
    }

    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();
        return response()->json(["message" => "success"]);
    }
}
