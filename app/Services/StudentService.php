<?php

namespace App\Services;

use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class StudentService
{
    public function __construct(
        private GradeService $gradeService
    ) {}

    public function getStudents($request)
    {
        $query = Student::query()
            ->whereHas("enrollments")
            ->with(["user", "enrollments.schoolClass"]);

        if ($request->filled("filter")) {
            $query->whereHas("enrollments", function ($q) use ($request) {
                $q->where("school_class_id", $request->filter);
            });
        }

        if ($request->filled("search")) {
            $query->whereHas("user", function ($q) use ($request) {
                $q->where("name", "like", "%" . $request->search . "%");
            });
        }

        return $query;
    }

    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $academicYearId = AcademicYear::activeId();
            $student = Student::where('nis', $data['nis'])->first();

            if (!$student) {

                $firstName = Str::of($data['name'])->before(' ')->lower();
                $username = "{$firstName}_{$data['nis']}";

                $user = User::create([
                    'name' => $data['name'],
                    'username' => $username,
                    'password' => Hash::make($data['nis']),
                    'photo' => 'default.png',
                    'role' => 'student',
                ]);

                $student = $user->student()->create(['nis' => $data['nis'],]);
            }

            $exists = $student->enrollments()->where('academic_year_id', $academicYearId)->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'nis' => 'Siswa sudah terdaftar pada tahun akademik aktif.',
                ]);
            }

            $enrollment = $student->enrollments()->create([
                'school_class_id' => $data['school_class_id'],
                'academic_year_id' => AcademicYear::activeId(),
            ]);

            $this->gradeService->generateForStudent(
                $student->id,
                $enrollment->school_class_id
            );

            return $student;
        });
    }

    public function update(Student $student, array $data): void
    {
        DB::transaction(function () use ($student, $data) {
            $user = $student->user;
            $firstName = Str::of($data['name'])->before(' ')->lower();

            $payload = [
                'name' => $data['name'],
                'username' => "{$firstName}_{$data['nis']}",
            ];

            $user->update($payload);
            $student->update(['nis' => $data['nis']]);

            $enrollment = $student->enrollments()->firstOrFail();
            $oldClassId = $enrollment->school_class_id;

            $enrollment->update([
                'school_class_id' => $data['school_class_id']
            ]);

            if ($oldClassId !== (int) $data['school_class_id']) {
                Grade::where('student_id', $student->id)->delete();
                $this->gradeService->generateForStudent(
                    $student->id,
                    $data['school_class_id']
                );
            }
        });
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $student->user->delete();
        });
    }

    public function import($file, int $schoolClassId): void
    {
        Excel::import(new StudentsImport($schoolClassId), $file);
    }

    public function export($schoolClassId = null)
    {
        $filename = $schoolClassId
            ? "students_class_{$schoolClassId}.xlsx"
            : "students.xlsx";

        return Excel::download(
            new StudentsExport($schoolClassId),
            $filename
        );
    }
}
