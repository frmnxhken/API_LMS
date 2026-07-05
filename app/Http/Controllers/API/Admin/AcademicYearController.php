<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYearRequest;
use App\Http\Requests\AcademicYearStatusRequest;
use App\Models\AcademicYear;
use App\Services\AcademicCalendarService;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function __construct(
        protected AcademicCalendarService $calendarService,
        protected AcademicYearService $academicService
    ) {}

    public function index()
    {
        return response()->json(["data" => AcademicYear::get()]);
    }

    public function store(AcademicYearRequest $request)
    {
        DB::transaction(function () use ($request) {
            $academicYear = $this->academicService->store($request);
            $this->calendarService->generate($academicYear);
        });

        return response()->json(["message" => "success"], 201);
    }

    public function show(AcademicYear $academicYear)
    {
        return response()->json([$academicYear]);
    }

    public function update(AcademicYearRequest $request, AcademicYear $academicYear)
    {
        DB::transaction(function () use ($request, $academicYear) {
            $academicYear->update($request->validated());
            $this->calendarService->generate($academicYear);
        });

        return response()->json(["message" => "success"]);
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return response()->json(["message" => "success"]);
    }

    public function activate(AcademicYear $academicYear)
    {
        $this->academicService->setActivate($academicYear);
        return response()->json(["message" => "success"]);
    }

    public function handleStatus(AcademicYear $academicYear, AcademicYearStatusRequest $request)
    {
        $academicYear->update($request->validated());
        return response()->json(["message" => "success"]);
    }

    public function current()
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        return response()->json($academicYear);
    }
}
