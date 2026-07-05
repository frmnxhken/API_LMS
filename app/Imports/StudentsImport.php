<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            $student = Student::where('nis', $row['nis'])->first();

            if (!$student) {
                $firstName = Str::of($row['nama'])->before(' ')->lower();
                $username = "{$firstName}_{$row['nis']}";

                $user = User::create([
                    'name'     => $row['nama'],
                    'username' => $username,
                    'password' => Hash::make($row['nis']),
                    'role'     => 'student',
                    'photo'    => 'default.png',
                ]);

                $student = $user->student()->create([
                    'nis' => $row['nis'],
                ]);
            }

            return $student;
        });
    }

    public function rules(): array
    {
        return [
            'nis'  => 'required',
            'nama' => 'required|string|max:255',
        ];
    }
}
