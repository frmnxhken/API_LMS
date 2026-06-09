<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\Attendance;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        $student = Auth::user()->student;

        Attendance::create(
            [
                'student_id' => $student->id,
                'date' => today(),
                'academic_year_id' => AcademicYear::where('is_active', true)->value('id'),
                'arrival_time' => now()->format('H:i:s'),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => 'present',
            ]
        );

        return response()->json([
            'message' => 'Attendance checked in successfully.'
        ]);
    }

    public function upsertStatus(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'status' => ['required', 'in:present,sick,permission,alpha'],
        ]);


        DB::transaction(function () use ($validated) {

            $period = CarbonPeriod::between(
                $validated['start_date'],
                $validated['end_date']
            );

            foreach ($period as $date) {

                $dateString = $date->toDateString();

                $isSchoolDay = AcademicCalendar::query()
                    ->whereDate('date', $dateString)
                    ->where('is_school_day', true)
                    ->exists();

                if (!$isSchoolDay) {
                    continue;
                }

                if ($validated['status'] === 'alpha') {

                    Attendance::query()
                        ->where('student_id', $validated['student_id'])
                        ->whereDate('date', $dateString)
                        ->delete();

                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'student_id' => $validated['student_id'],
                        'date' => $dateString,
                    ],
                    [
                        'academic_year_id' => AcademicYear::activeId(),
                        'status' => $validated['status'],
                        'arrival_time' => null,
                        'latitude' => null,
                        'longitude' => null,
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Attendance updated successfully'
        ]);
    }
}
