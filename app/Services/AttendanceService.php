<?php

namespace App\Services;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\Attendance;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function show()
    {
        $today = today();
        $student = Auth::user()->student;

        return [
            "meta" => AcademicCalendar::whereDate('date', $today)->first(),
            "data" => Attendance::where("student_id", $student->id)
                ->latest()
                ->limit(5)
                ->get()
        ];
    }

    public function checkIn(array $data)
    {
        $student = Auth::user()->student;
        $today = today();
        $now = now();

        $this->ensureSchoolDay($today);
        $this->ensureNotCheckedIn($student->id, $today);
        $this->ensureWithinRadius($data['latitude'], $data['longitude']);

        $start = Carbon::createFromTimeString('05:00:00');
        $lateLimit = Carbon::createFromTimeString('07:00:00');
        $cutOff = Carbon::createFromTimeString('08:00:00');

        if ($now->lt($start)) {
            abort(422, 'Absen belum dibuka');
        }

        if ($now->gt($cutOff)) {
            abort(422, 'Absen sudah ditutup');
        }

        if ($now->lte($lateLimit)) {
            $status = 'present';
        } else {
            $status = 'late';
        }

        Attendance::create([
            'student_id' => $student->id,
            'date' => $today,
            'academic_year_id' => AcademicYear::activeId(),
            'arrival_time' => now()->format('H:i:s'),
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'status' => $status,
        ]);
    }

    public function upsertStatus(array $data)
    {
        DB::transaction(function () use ($data) {

            $period = CarbonPeriod::between(
                $data['start_date'],
                $data['end_date']
            );

            foreach ($period as $date) {

                $dateString = $date->toDateString();

                if (!$this->isSchoolDay($dateString)) {
                    continue;
                }

                if ($data['status'] === 'alpha') {
                    Attendance::where('student_id', $data['student_id'])
                        ->whereDate('date', $dateString)
                        ->delete();
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'student_id' => $data['student_id'],
                        'date' => $dateString,
                    ],
                    [
                        'academic_year_id' => AcademicYear::activeId(),
                        'status' => $data['status'],
                        'arrival_time' => null,
                        'latitude' => null,
                        'longitude' => null,
                    ]
                );
            }
        });
    }

    private function ensureSchoolDay($date)
    {
        $isSchoolDay = AcademicCalendar::whereDate('date', $date)
            ->where('is_school_day', true)
            ->exists();

        if (!$isSchoolDay) {
            abort(422, 'Hari ini bukan hari sekolah');
        }
    }

    private function ensureNotCheckedIn($studentId, $date)
    {
        if (
            Attendance::where('student_id', $studentId)
            ->whereDate('date', $date)
            ->exists()
        ) {
            abort(422, 'Sudah check-in hari ini');
        }
    }

    private function ensureWithinRadius($lat, $lng)
    {
        $distance = $this->haversine(
            $lat,
            $lng,
            config('attendance.latitude'),
            config('attendance.longitude')
        );

        if ($distance > config('attendance.radius')) {
            abort(422, 'Di luar area sekolah');
        }
    }

    private function isSchoolDay($date)
    {
        return AcademicCalendar::whereDate('date', $date)
            ->where('is_school_day', true)
            ->exists();
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) ** 2;

        return 2 * $earthRadius * atan2(sqrt($a), sqrt(1 - $a));
    }
}
