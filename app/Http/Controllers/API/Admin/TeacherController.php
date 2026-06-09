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

class TeacherController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware("academicYearDraft", only: ["update", "destroy", "import"]),
        ];
    }

    public function __construct(protected TeacherService $teacherService) {}

    public function index(Request $request)
    {
        $query = Teacher::query()->with("user");

        if ($request->filled("search")) {
            $query->whereHas("user", function ($q) use ($request) {
                $q->where("name", "like", "%" . $request->search . "%");
            });
        }

        return TeacherResource::collection($query->paginate(10));
    }

    public function store(StoreTeacherRequest $request)
    {
        $this->teacherService->create($request->validated());
        return response()->json(["message" => "success"], 201);
    }

    public function show(Teacher $teacher)
    {
        return new TeacherResource($teacher->load("user"));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $this->teacherService->update($teacher, $request->validated());
        return response()->json(["message" => "success"]);
    }

    public function destroy(Teacher $teacher)
    {
        $this->teacherService->delete($teacher);
        return response()->json(["message" => "success"]);
    }

    public function import(ImportTeacherRequest $request)
    {
        try {
            $this->teacherService->import($request->file("file"));
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

    public function export()
    {
        return $this->teacherService->export();
    }
}
