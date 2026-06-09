<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectRequest;
use App\Models\Subject;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SubjectController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware("academicYearDraft", only: ["update", "destroy"]),
        ];
    }

    public function index()
    {
        return response()->json(Subject::get(), 200);
    }

    public function store(SubjectRequest $request)
    {
        Subject::create($request->validated());
        return response()->json(["message" => "success"], 201);
    }

    public function update(SubjectRequest $request, Subject $subject)
    {
        $subject->update($request->validated());
        return response()->json(["message" => "success"], 201);
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return response()->json(["message" => "success"]);
    }
}
