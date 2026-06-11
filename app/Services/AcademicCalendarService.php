<?php

namespace App\Services;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AcademicCalendarService
{
    public function generate(AcademicYear $academicYear)
    {

        $period = CarbonPeriod::create(
            $academicYear->start,
            $academicYear->end
        );

        DB::transaction(function () use ($academicYear, $period) {
            AcademicCalendar::where('academic_year_id', $academicYear->id)->delete();

            $data = [];

            foreach ($period as $date) {
                $isWeekend = $date->isWeekend();

                $data[] = [
                    'academic_year_id' => $academicYear->id,
                    'date' => $date->toDateString(),
                    'is_school_day' => !$isWeekend,
                    'description' => $isWeekend ? 'Libur Akhir Pekan' : 'Hari Sekolah',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($data, 100) as $chunk) {
                AcademicCalendar::insert($chunk);
            }
        });
    }
}
