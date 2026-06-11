<?php

namespace App\Services;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function store($request)
    {
        $hasActive = AcademicYear::where('is_active', true)->exists();
        $data = $request->validated();

        if (!$hasActive) {
            $data['is_active'] = true;
        }

        $academicYear = AcademicYear::create($data);
        return $academicYear;
    }

    public function setActivate($academicYear)
    {
        DB::transaction(function () use ($academicYear) {
            AcademicYear::query()->update(["is_active" => 0]);
            $academicYear->update(["is_active" => 1,]);
        });
    }
}
