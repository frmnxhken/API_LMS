<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsExport implements FromCollection, WithHeadings
{
    protected ?int $schoolClassId;

    public function __construct(?int $schoolClassId = null)
    {
        $this->schoolClassId = $schoolClassId;
    }

    public function collection()
    {
        $query = Student::query()
            ->with([
                'user',
                'enrollments.schoolClass'
            ]);

        if ($this->schoolClassId) {
            $query->whereHas('enrollments', function ($q) {
                $q->where(
                    'school_class_id',
                    $this->schoolClassId
                );
            });
        }

        return $query->get()->map(function ($student) {

            $enrollment = $student->enrollments->first();

            return [
                'nis' => $student->nis,
                'name' => $student->user->name,
                'username' => $student->user->username,
                'class' => $enrollment?->schoolClass?->level
                    . ' '
                    . $enrollment?->schoolClass?->major
                    . ' '
                    . $enrollment?->schoolClass?->section,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama',
            'Username',
            'Kelas',
        ];
    }
}
