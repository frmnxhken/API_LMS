<?php

namespace App\Http\Controllers\API;

use App\Exports\GradeExport;
use App\Http\Controllers\Controller;
use App\Services\GradeService;
use Maatwebsite\Excel\Facades\Excel;

class GradeController extends Controller
{
    public function __construct(protected GradeService $service) {}

    public function index($id_class_subject)
    {
        $grades = $this->service->getClassGrades($id_class_subject);
        return response()->json($grades);
    }

    public function show($id_class_subject, $id_student)
    {
        $grade = $this->service->getStudentDetail($id_class_subject, $id_student);
        return response()->json($grade);
    }

    public function export($id_class_subject)
    {
        return Excel::download(
            new GradeExport($id_class_subject),
            'nilai.xlsx'
        );
    }
}
