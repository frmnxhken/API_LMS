<?php

namespace App\Services;

use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StudentService
{
    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $firstName = Str::of($data['name'])->before(' ')->lower();
            $username = "{$firstName}_{$data['nis']}";

            $user = User::create([
                'name' => $data['name'],
                'username' => $username,
                'password' => Hash::make($data['password']),
                'photo' => 'default.png',
                'role' => 'student',
            ]);

            $student = $user->student()->create(['nis' => $data['nis'],]);
            $student->enrollments()->create([
                'school_class_id' => $data['school_class_id'],
                'academic_year_id' => AcademicYear::activeId(),
            ]);

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

            if (!empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);
            $student->update(['nis' => $data['nis']]);

            $student->enrollments()
                ->where('academic_year_id', AcademicYear::activeId())
                ->update(['school_class_id' => $data['school_class_id']]);
        });
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $user = $student->user;
            $student->enrollments()->delete();
            $student->delete();
            $user->delete();
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
