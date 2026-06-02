<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use Illuminate\Support\Facades\DB;

class TeachingAssignmentService
{
    public function create(array $data): ClassSubject
    {
        return DB::transaction(function () use ($data) {
            $data['academic_year_id'] = AcademicYear::activeId();
            return ClassSubject::create($data);
        });
    }

    public function update(ClassSubject $classSubject, array $data): void
    {
        DB::transaction(function () use ($classSubject, $data) {
            $classSubject->update($data);
        });
    }

    public function delete(ClassSubject $classSubject): void
    {
        DB::transaction(function () use ($classSubject) {
            $classSubject->delete();
        });
    }
}
