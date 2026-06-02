<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index()
    {
        return response()->json(['data' => AcademicYear::get()]);
    }

    public function store(AcademicYearRequest $request)
    {
        AcademicYear::create($request->validated());
        return response()->json(['message' => 'success',], 201);
    }

    public function show(AcademicYear $academicYear)
    {
        return response()->json([$academicYear]);
    }

    public function update(AcademicYearRequest $request, AcademicYear $academicYear)
    {
        $academicYear->update($request->validated());
        return response()->json(['message' => 'success']);
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return response()->json(['message' => 'success']);
    }

    public function activate(AcademicYear $academicYear)
    {
        DB::transaction(function () use ($academicYear) {
            AcademicYear::query()->update(['is_active' => 0]);
            $academicYear->update(['is_active' => 1,]);
        });

        return response()->json(['message' => 'success']);
    }
}
