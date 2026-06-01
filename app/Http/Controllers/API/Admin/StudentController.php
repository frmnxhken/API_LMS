<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentCollection;
use App\Http\Resources\StudentResource;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::whereHas("enrollments")->with(["user", "enrollments.schoolClass"]);
        if ($request->filled("filter")) {
            $query->whereHas("enrollments", function ($q) use ($request) {
                $q->where("school_class_id", $request->filter);
            });
        }

        $students = $query->paginate(10);
        return new StudentCollection($students);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "nis" => "required|unique:students,id",
            "name" => "required",
            "school_class_id" => "required"
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $firstName = Str::of($request->name)->before(' ')->lower();
            $username  = "{$firstName}_{$request->nis}";

            $user = User::create([
                "name"     => $request->name,
                "username" => $username,
                "password" => Hash::make($request->password),
                "photo"    => "default.png",
                "role"     => "student"
            ]);

            $student = $user->student()->create([
                "nis" => $request->nis
            ]);

            $student->enrollments()->create([
                'school_class_id'  => $request->school_class_id,
                'academic_year_id' => AcademicYear::activeId(),
            ]);

            return $user;
        });
    }

    public function show($id)
    {
        $student = Student::with(['user', 'enrollments.schoolClass', 'enrollments.academicYear'])
            ->findOrFail($id);

        return new StudentResource($student);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $user = $student->user;

        $validation = Validator::make($request->all(), [
            "nis" => "required|unique:students,nis," . $id,
            "name" => "required|string|max:255",
            "school_class_id" => "required|exists:school_classes,id"
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()], 422);
        }

        return DB::transaction(function () use ($request, $user, $student) {
            $user->update([
                "name" => $request->name,
                "password" => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            $student->update([
                "nis" => $request->nis
            ]);

            $student->enrollments()
                ->where("academic_year_id", AcademicYear::activeId())
                ->update([
                    "school_class_id" => $request->school_class_id
                ]);

            return response()->json(["message" => "Data siswa berhasil diperbarui"]);
        });
    }

    public function destroy(Request $request, $id)
    {
        return DB::transaction(function () use ($id) {
            $student = Student::findOrFail($id);
            $user = $student->user;
            $student->enrollments()->delete();
            $student->delete();
            $user->delete();

            return response()->json(["message" => "Siswa berhasil dihapus"]);
        });
    }

    public function import(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'school_class_id' => 'required|exists:school_classes,id'
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()], 422);
        }

        try {
            $file = $request->file('file');

            Excel::import(
                new StudentsImport($request->school_class_id),
                $file
            );

            return response()->json([
                'message' => 'Data siswa berhasil diimport secara massal.'
            ], 200);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(", ", $failure->errors());
            }

            return response()->json([
                'errors' => $errorMessages
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat membaca file.',
                'debug'   => $th->getMessage()
            ], 500);
        }
    }
}
