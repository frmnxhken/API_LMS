<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceUpsertRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    public function show()
    {
        return response()->json($this->service->show());
    }

    public function checkIn(Request $request)
    {
        $this->service->checkIn($request->all());

        return response()->json([
            'message' => 'Attendance checked in successfully.'
        ]);
    }

    public function upsertStatus(AttendanceUpsertRequest $request)
    {
        $this->service->upsertStatus($request->validated());
        return response()->json([
            'message' => 'Attendance updated successfully'
        ]);
    }
}
