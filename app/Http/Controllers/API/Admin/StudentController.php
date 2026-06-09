<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportStudentRequest;
use App\Http\Requests\ImportStudentRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentCollection;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StudentController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware("academicYearDraft", only: ["update", "destroy", "import"]),
        ];
    }

    public function __construct(protected StudentService $service) {}

    public function index(Request $request)
    {
        $students = $this->service->getStudents($request);
        return new StudentCollection($students->paginate(10));
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->service->create($request->validated());
        return response()->json(["message" => "success"], 201);
    }

    public function show(Student $student)
    {
        $student->load(["user", "enrollments.schoolClass"]);
        return new StudentResource($student);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->service->update($student, $request->validated());
        return response()->json(["message" => "Success"]);
    }

    public function destroy(Student $student)
    {
        $this->service->delete($student);
        return response()->json(["message" => "Success"]);
    }

    public function import(ImportStudentRequest $request)
    {
        try {
            $this->service->import($request->file("file"), $request->school_class_id);
            return response()->json(["message" => "Success"]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = "Baris {$failure->row()}: "
                    . implode(", ", $failure->errors());
            }

            return response()->json(["errors" => $errors], 422);
        } catch (\Throwable $e) {
            return response()->json(["message" => "Terjadi kesalahan saat import file."], 500);
        }
    }

    public function export(ExportStudentRequest $request)
    {
        return $this->service->export(
            $request->school_class_id
        );
    }
}
