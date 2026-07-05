<?php

namespace App\Services;

use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StudentService
{
    public function getStudents($request)
    {
        $query = Student::query()->with(["user"]);

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
        });
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $student->user->delete();
        });
    }

    public function import($file): void
    {
        Excel::import(new StudentsImport(), $file);
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
