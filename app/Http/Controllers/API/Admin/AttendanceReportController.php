<?php

namespace App\Http\Controllers\API\Admin;

use App\Exports\AttendanceMonthlyExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceHistoryResource;
use App\Http\Resources\AttendanceTodayResource;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    public function summary()
    {
        $today = today();

        return response()->json([
            'present' => Attendance::whereDate('date', $today)
                ->where('status', 'present')
                ->count(),

            'sick' => Attendance::whereDate('date', $today)
                ->where('status', 'sick')
                ->count(),

            'permission' => Attendance::whereDate('date', $today)
                ->where('status', 'permission')
                ->count(),

            'not_checked_in' => Student::whereDoesntHave(
                'attendances',
                fn($query) => $query->whereDate('date', $today)
            )->count(),
        ]);
    }

    public function today(Request $request)
    {
        $date = $request->date ?? today();

        $query = Student::with([
            'attendances' => fn($query) =>
            $query->whereDate('date', $date),
            'user'
        ]);

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

        return AttendanceTodayResource::collection($query->paginate(30));
    }

    public function history(Request $request)
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

        return  AttendanceHistoryResource::collection($query->latest('date')->paginate(30));
    }

    public function studentReport($studentId)
    {
        return Attendance::with('student')
            ->where('student_id', $studentId)
            ->latest('date')
            ->paginate(10);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);

        return Excel::download(
            new AttendanceMonthlyExport(
                $validated['academic_year_id'],
                $validated['school_class_id'],
                $validated['month']
            ),
            'attendance-report.xlsx'
        );
    }
}
