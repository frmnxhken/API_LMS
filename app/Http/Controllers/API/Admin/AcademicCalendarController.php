<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicCalendarRequest;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
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
        AcademicCalendarRequest $request
    ) {
        $academicCalendar->update($request->validated());
        return response()->json(["message" => "success"]);
    }

    public function weekly()
    {
        $today = Carbon::today();

        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        $week = AcademicCalendar::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date')->get();

        return response()->json($week);
    }
}
