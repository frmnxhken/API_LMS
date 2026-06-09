<?php

namespace App\Http\Controllers\API\Admin;

use App\Exports\AttendanceMonthlyExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceExportRequest;
use App\Http\Resources\AttendanceHistoryResource;
use App\Http\Resources\AttendanceSummaryResource;
use App\Http\Resources\AttendanceTodayResource;
use App\Services\AttendanceReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    public function __construct(
        protected AttendanceReportService $service
    ) {}

    public function summary()
    {
        $stats = $this->service->getSummary();
        return new AttendanceSummaryResource($stats);
    }

    public function today(Request $request)
    {
        $attendances = $this->service->getTodayAttendance($request);
        return AttendanceTodayResource::collection($attendances->paginate(30));
    }

    public function history(Request $request)
    {
        $histories = $this->service->getHistory($request);
        return  AttendanceHistoryResource::collection($histories->latest('date')->paginate(30));
    }

    public function export(AttendanceExportRequest $request)
    {
        return Excel::download(
            new AttendanceMonthlyExport(
                $request->academic_year_id,
                $request->school_class_id,
                $request->month
            ),
            'attendance-report.xlsx'
        );
    }
}
