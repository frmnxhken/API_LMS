<?php

namespace App\Imports;

use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $schoolClassId;

    public function __construct($schoolClassId)
    {
        $this->schoolClassId = $schoolClassId;
    }

    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            $firstName = Str::of($row['nama'])->before(' ')->lower();
            $username  = "{$firstName}_{$row['nis']}";

            $user = User::create([
                'name'     => $row['nama'],
                'username' => $username,
                'password' => Hash::make('tes_123'),
                'role'     => 'student',
                'photo'    => 'default.png'
            ]);

            $student = $user->student()->create([
                'nis' => $row['nis']
            ]);

            $student->enrollments()->create([
                'school_class_id'  => $this->schoolClassId,
                'academic_year_id' => AcademicYear::activeId(),
            ]);

            return $user;
        });
    }

    public function rules(): array
    {
        return [
            'nis'  => 'required|unique:students,nis',
            'nama' => 'required|string|max:255',
        ];
    }
}
