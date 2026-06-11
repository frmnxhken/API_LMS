<?php

namespace App\Http\Controllers\API;

use App\Exports\GradeExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Http\Resources\StudentResource;
use App\Models\ClassSubject;
use App\Models\ExamAssignment;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\Post;
use App\Models\Student;
use App\Models\Submission;
use App\Models\WeightSumScore;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GradeController extends Controller
{
    public function index($id_class_subject)
    {
        $weights = WeightSumScore::where("class_subject_id", $id_class_subject)->first();

        $grades = Grade::with('student.user')
            ->where('class_subject_id', $id_class_subject)
            ->get();

        $classSubject = ClassSubject::with([
            'subject',
            'schoolClass'
        ])->findOrFail($id_class_subject);

        $totalAssignment = Post::where('class_subject_id', $id_class_subject)
            ->where('type', 'assignment')
            ->count();

        $exams = ExamAssignment::where('class_subject_id', $id_class_subject)
            ->with('exam:id,type')
            ->get();

        $totalDaily = $exams->where('exam.type', 'harian')->count();
        $totalUTS = $exams->where('exam.type', 'uts')->count();
        $totalUAS = $exams->where('exam.type', 'uas')->count();

        $grades->transform(function ($grade) use (
            $weights,
            $totalAssignment,
            $totalDaily,
            $totalUTS,
            $totalUAS
        ) {

            $assignmentAvg = $totalAssignment > 0
                ? $grade->assignment_total_score / $totalAssignment
                : 0;
            $dailyAvg = $totalDaily > 0
                ? $grade->daily_total_score / $totalDaily
                : 0;
            $uts = $totalUTS > 0
                ? $grade->uts_score : 0;
            $uas = $totalUAS > 0
                ? $grade->uas_score : 0;
            $grade->final_score =
                round(($assignmentAvg * $weights->assignment_weight) +
                    ($dailyAvg * $weights->daily_weight) +
                    ($uts * $weights->uts_weight) +
                    ($uas * $weights->uas_weight), 2);

            $grade->assignment = $assignmentAvg;
            $grade->daily = $dailyAvg;
            $grade->uts = $uts;
            $grade->uas = $uas;
            return $grade;
        });

        $sortedGrades = $grades->sortByDesc('final_score')->values();

        return response()->json([
            'meta' => $classSubject,
            'data' => GradeResource::collection($sortedGrades),
        ]);
    }

    public function show($id_class_subject, $id_student)
    {
        $student = Student::with('user')
            ->findOrFail($id_student);

        $subject = ClassSubject::with("subject")->where("id", $id_class_subject)->first();
        $assignments = Submission::with([
            'post'
        ])
            ->where('student_id', $id_student)
            ->whereHas('post', function ($query) use ($id_class_subject) {
                $query->where('class_subject_id', $id_class_subject);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'score' => $item->score,
                    'date' => $item->created_at,
                ];
            });

        $exams = ExamAttempt::with([
            'assignment.exam'
        ])->where('student_id', $id_student)
            ->whereHas('assignment', function ($query) use ($id_class_subject) {
                $query->where('class_subject_id', $id_class_subject);
            })->get()->map(function ($item) {
                return [
                    'type' => $item->assignment->exam->type,
                    'score' => $item->score,
                    'date' => $item->started_at,
                ];
            });

        return response()->json([
            'subject' => $subject->subject->name,
            'student' => new StudentResource($student),
            'assignments' => $assignments,
            'exams' => $exams,
        ]);
    }

    public function export($id_class_subject)
    {
        return Excel::download(
            new GradeExport($id_class_subject),
            'nilai.xlsx'
        );
    }
}
