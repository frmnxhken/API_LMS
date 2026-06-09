<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicCalendarRequest;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AcademicCalendarController extends Controller
{
    public function index()
    {
        $academicYear = AcademicYear::active();
        return response()->json([
            "meta" => $academicYear,
            "dates" => AcademicCalendar::where('academic_year_id', $academicYear->id)->get()
        ]);
    }

    public function update(
        AcademicCalendar $academicCalendar,
        AcademicCalendarRequest $reuest
    ) {
        $academicCalendar->update($reuest->validated());
        return response()->json(["message" => "success"]);
    }

    public function weekly()
    {
        $today = Carbon::today();

        $calendars = AcademicCalendar::query()
            ->whereMonth('date', $today->month)
            ->whereYear('date', $today->year)
            ->orderBy('date')
            ->get();

        $currentIndex = $calendars->search(function ($item) use ($today) {
            return Carbon::parse($item->date)->isSameDay($today);
        });

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $startIndex = floor($currentIndex / 7) * 7;
        $week = $calendars->slice($startIndex, 7)->values();

        return response()->json($week);
    }
}
