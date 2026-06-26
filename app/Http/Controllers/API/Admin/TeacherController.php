<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportTeacherRequest;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware("academicYear:draft", except: ["index", "list", "export"]),
        ];
    }

    public function __construct(protected TeacherService $service) {}

    public function index(Request $request)
    {
        $teachers = $this->service->getTeachers($request);
        return TeacherResource::collection($teachers->paginate(10));
    }

    public function list(Request $request)
    {
        $teachers = $this->service->getTeachers($request);
        return TeacherResource::collection($teachers->get());
    }

    public function store(StoreTeacherRequest $request)
    {
        $this->service->create($request->validated());
        return response()->json(["message" => "success"], 201);
    }

    public function show(Teacher $teacher)
    {
        return new TeacherResource($teacher->load("user"));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $this->service->update($teacher, $request->validated());
        return response()->json(["message" => "success"]);
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return response()->json(["message" => "success"]);
    }

    public function import(ImportTeacherRequest $request)
    {
        try {
            $this->service->import($request->file("file"));
            return response()->json(["message" => "success"]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = "Baris {$failure->row()}: "
                    . implode(", ", $failure->errors());
            }

            return response()->json(["errors" => $errors], 422);
        }
    }

    public function resetPassword(Teacher $teacher)
    {
        $teacher->user->update(["password" => Hash::make($teacher->nip)]);
        return response()->json(["message" => "Password berhasil direset."], 201);
    }

    public function export()
    {
        return $this->service->export();
    }
}
