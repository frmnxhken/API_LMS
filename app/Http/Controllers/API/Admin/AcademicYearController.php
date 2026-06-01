<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $academicYears = AcademicYear::get();

        return response()->json([
            'success' => true,
            'message' => 'List data tahun akademik',
            'data'    => $academicYears
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $academicYear = AcademicYear::create([
            'name'      => $request->name,
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tahun akademik berhasil ditambahkan',
            'data'    => $academicYear
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $academicYear = AcademicYear::find($id);

        if (!$academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail data tahun akademik',
            'data'    => $academicYear
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
        ]);

        $academicYear = AcademicYear::find($id);

        if ($validator->fails()) {
            return response()->json([
                'errors'  => $validator->errors()
            ], 422);
        }

        $academicYear->update([
            'name'      => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tahun akademik berhasil diperbarui',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $academicYear = AcademicYear::find($id);

        if (!$academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $academicYear->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tahun akademik berhasil dihapus'
        ], 200);
    }
}
