<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeachersExport implements
    FromCollection,
    WithHeadings
{
    public function collection()
    {
        return Teacher::with('user')
            ->get()
            ->map(fn($teacher) => [
                'nip' => $teacher->nip,
                'name' => $teacher->user?->name,
                'username' => $teacher->user?->username,
                'phone' => $teacher->phone,
            ]);
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama',
            'Username',
            'Telepon',
        ];
    }
}
