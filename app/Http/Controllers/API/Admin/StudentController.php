<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportStudentRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentCollection;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(protected StudentService $studentService) {}

    public function index(Request $request)
    {
        $query = Student::query()
            ->whereHas('enrollments')
            ->with(['user', 'enrollments.schoolClass']);

        if ($request->filled('filter')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('school_class_id', $request->filter);
            });
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        return new StudentCollection($query->paginate(10));
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->studentService->create($request->validated());
        return response()->json(['message' => 'success'], 201);
    }

    public function show(Student $student)
    {
        $student->load(['user', 'enrollments.schoolClass', 'enrollments.academicYear']);
        return new StudentResource($student);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->studentService->update($student, $request->validated());
        return response()->json(['message' => 'Success']);
    }

    public function destroy(Student $student)
    {
        $this->studentService->delete($student);
        return response()->json(['message' => 'Success']);
    }

    public function import(ImportStudentRequest $request)
    {
        try {
            $this->studentService->import($request->file('file'), $request->school_class_id);
            return response()->json(['message' => 'Success']);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = "Baris {$failure->row()}: "
                    . implode(', ', $failure->errors());
            }

            return response()->json(['errors' => $errors], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat import file.'], 500);
        }
    }

    public function export(Request $request)
    {
        return $this->studentService->export(
            $request->school_class_id
        );
    }
}
