<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Services\GradeService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $schoolClassId;
    protected GradeService $gradeService;

    public function __construct($schoolClassId)
    {
        $this->schoolClassId = $schoolClassId;
        $this->gradeService = app(GradeService::class);
    }

    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {

            $academicYearId = AcademicYear::activeId();

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

            $enrollment = $student->enrollments()->where('academic_year_id', $academicYearId)->first();

            if (!$enrollment) {
                $student->enrollments()->create([
                    'school_class_id'  => $this->schoolClassId,
                    'academic_year_id' => $academicYearId,
                ]);

                $this->gradeService->generateForStudent($student->id, $this->schoolClassId);
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
