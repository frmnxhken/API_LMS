<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Attendance;

class AttendanceReportService
{
    public function getSummary()
    {
        $today = today();

        $stats = Attendance::whereDate('date', $today)->get()->countBy('status');
        $stats->put('not_checked_in', Student::whereDoesntHave('attendances', fn($q) => $q
            ->whereDate('date', $today))->count());
        return $stats;
    }

    public function getTodayAttendance($request)
    {
        $date = $request->date ?? today();

        $query = Student::with(['attendances' => fn($query) =>
        $query->whereDate('date', $date), 'user']);

        if ($request->filled("search")) {
            $query->where("nis", 'like', '%' . $request->search . '%')->orWhereHas('user', function ($user) use ($request) {
                $user->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status === 'alpha') {
            $query->whereDoesntHave('attendances', function ($query) use ($date) {
                $query->whereDate('date', $date);
            });
        }

        if ($request->filled('status') && $request->status !== 'alpha') {
            $query->whereHas('attendances', function ($query) use ($date, $request) {
                $query->whereDate('date', $date)
                    ->where('status', $request->status);
            });
        }

        return $query;
    }

    public function getHistory($request)
    {
        $query = Attendance::with('student.user');

        if ($request->filled('search')) {
            $query->whereHas('student', function ($student) use ($request) {
                $student->where('nis', 'like', '%' . $request->search . '%')->orWhereHas('user', function ($user) use ($request) {
                    $user->where('name', 'like', '%' . $request->search . '%');
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date)->oldest('date');
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date)->oldest('date');
        }

        return $query;
    }
}
