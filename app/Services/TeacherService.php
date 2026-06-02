<?php

namespace App\Services;

use App\Exports\TeachersExport;
use App\Imports\TeachersImport;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class TeacherService
{
    public function create(array $data): Teacher
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['nip'],
                'password' => Hash::make($data['password']),
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
            $payload = [
                'name' => $data['name'],
                'username' => $data['nip'],
            ];

            if (!empty($data['password'])) {
                $payload['password'] =
                    Hash::make($data['password']);
            }

            $teacher->user->update($payload);
            $teacher->update([
                'nip' => $data['nip'],
                'phone' => $data['phone'],
            ]);
        });
    }

    public function delete(Teacher $teacher): void
    {
        DB::transaction(function () use ($teacher) {
            $teacher->user->delete();
            $teacher->delete();
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
