<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AcademicYearController extends Controller
{
    public function __construct(
        protected AcademicCalendarService $calendarService
    ) {}

    public function index()
    {
        return response()->json(["data" => AcademicYear::get()]);
    }

    public function store(AcademicYearRequest $request)
    {
        /** 
         * 
         * Lakukan validasi awal pastikan tahun hanya selisih 1 tahun saja
         * generate kalender akademik ignore weekend 
         * 
         */
        DB::transaction(function () use ($request) {
            $academicYear = AcademicYear::create($request->validated());
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
        /** 
         * 
         * Saat di update dan tahun berubah maka hapus kalender akademik
         * lalu generate ulang
         * 
         */
        $academicYear->update($request->validated());
        return response()->json(["message" => "success"]);
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return response()->json(["message" => "success"]);
    }

    public function activate(AcademicYear $academicYear)
    {
        DB::transaction(function () use ($academicYear) {
            AcademicYear::query()->update(["is_active" => 0]);
            $academicYear->update(["is_active" => 1,]);
        });

        return response()->json(["message" => "success"]);
    }

    public function handleStatus(AcademicYear $academicYear, Request $request)
    {
        $validation = Validator::make($request->all(), [
            "status" => "required|in:draft,active,completed",
        ]);

        if ($validation->fails()) {
            return response()->json(["errors" => $validation->errors()], 422);
        }

        $academicYear->update(["status" => $request->status]);
        return response()->json(["message" => "success"]);
    }
}
