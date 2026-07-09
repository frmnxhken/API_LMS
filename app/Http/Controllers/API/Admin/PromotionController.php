<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        $fromAcademic = AcademicYear::where('status', 'completed')->latest('end')->first();
        $toAcademic = AcademicYear::where('status', 'draft')->latest('start')->first();

        if (!$fromAcademic || !$toAcademic) {
            return response()->json([
                'message' => 'Pastikan terdapat tahun ajaran Completed sebagai asal dan tahun ajaran Draft sebagai tujuan.'
            ], 422);
        }

        if ($fromAcademic->start >= $toAcademic->start) {
            return response()->json([
                'message' => 'Tahun ajaran tujuan harus berada setelah tahun ajaran asal.'
            ], 422);
        }

        $classes = StudentEnrollment::query()
            ->with('schoolClass')
            ->where('academic_year_id', $fromAcademic->id)
            ->select('school_class_id')
            ->distinct()
            ->get();

        $data = $classes
            ->map(function ($item) use ($fromAcademic) {

                $class = $item->schoolClass;

                $selectedClass = SchoolClass::query()
                    ->where('major', $class->major)
                    ->where('level', $class->level + 1)
                    ->where('section', $class->section)
                    ->first();

                if ($class->level >= 12) {
                    return null;
                }

                $targets = SchoolClass::query()
                    ->where('major', $class->major)
                    ->where('level', $class->level + 1)
                    ->orderBy('section')
                    ->get();

                return [
                    'from_class' => [
                        'id' => $class->id,
                        'name' => "{$class->level} {$class->major} {$class->section}",
                    ],

                    'student_count' => StudentEnrollment::query()
                        ->where('academic_year_id', $fromAcademic->id)
                        ->where('school_class_id', $class->id)
                        ->count(),

                    'selected_class_id' => $selectedClass?->id,
                    'available_classes' => $targets->map(fn($target) => [
                        'id' => $target->id,
                        'name' => "{$target->level} {$target->major} {$target->section}"
                    ])
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'from_academic_year' => $fromAcademic,
            'to_academic_year' => $toAcademic,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            foreach ($request->mappings as $mapping) {
                $students = StudentEnrollment::query()
                    ->where('academic_year_id', $request->from_academic_year_id)
                    ->where('school_class_id', $mapping['from_class_id'])
                    ->get();

                foreach ($students as $student) {
                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $student->student_id,
                            'academic_year_id' => $request->to_academic_year_id,
                        ],
                        [
                            'school_class_id' => $mapping['to_class_id'],
                        ]
                    );
                }
            }

            AcademicYear::where('id', $request->from_academic_year_id)->update(['is_active' => false]);
            AcademicYear::where('id', $request->to_academic_year_id)->update(['is_active' => true]);
        });

        return response()->json([
            'message' => 'Kenaikan kelas berhasil.'
        ]);
    }
}
