<?php

namespace App\Services;

use App\Exports\TeachersExport;
use App\Imports\TeachersImport;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class TeacherService
{
    public function getTeachers($request)
    {
        $query = Teacher::query()->with("user");

        if ($request->filled("search")) {
            $query->whereHas("user", function ($q) use ($request) {
                $q->where("name", "like", "%" . $request->search . "%");
            });
        }

        return $query;
    }

    public function create(array $data): Teacher
    {
        return DB::transaction(function () use ($data) {
            $firstName = Str::of($data['name'])->before(' ')->lower();
            $username  = "{$firstName}_{$data['nip']}";

            $user = User::create([
                'name' => $data['name'],
                'username' => $username,
                'password' => Hash::make($data['nip']),
                'photo' => 'default.png',
                'role' => 'teacher',
            ]);

            return $user->teacher()->create([
                'nip' => $data['nip'],
                'phone' => $data['phone'],
            ]);
        });
    }

    public function update(Teacher $teacher, array $data): void
    {
        DB::transaction(function () use ($teacher, $data) {
            $firstName = Str::of($data['name'])->before(' ')->lower();
            $username  = "{$firstName}_{$data['nip']}";

            $payload = [
                'name' => $data['name'],
                'username' => $username,
            ];

            $teacher->user->update($payload);
            $teacher->update([
                'nip' => $data['nip'],
                'phone' => $data['phone'],
            ]);
        });
    }

    public function import($file)
    {
        Excel::import(new TeachersImport, $file);
    }

    public function export()
    {
        return Excel::download(new TeachersExport(), 'teachers.xlsx');
    }
}
