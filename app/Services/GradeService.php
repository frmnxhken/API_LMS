<?php

namespace App\Services;

use App\Http\Resources\GradeResource;
use App\Http\Resources\StudentResource;
use App\Models\ClassSubject;
use App\Models\ExamAssignment;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\Post;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Submission;
use App\Models\WeightSumScore;

class GradeService
{
    public function generateForStudent($studentId, $schoolClassId)
    {
        $classSubjectIds = ClassSubject::where('school_class_id', $schoolClassId)->pluck('id');

        if ($classSubjectIds->isEmpty()) {
            return;
        }

        $grades = [];

        foreach ($classSubjectIds as $classSubjectId) {
            $grades[] = [
                'student_id'       => $studentId,
                'class_subject_id' => $classSubjectId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        Grade::upsert($grades, ['student_id', 'class_subject_id']);
    }

    public function generateForClassSubject($classSubjectId)
    {
        $classSubject = ClassSubject::findOrFail($classSubjectId);
        $grades = StudentEnrollment::where('school_class_id', $classSubject->school_class_id)
            ->pluck('student_id')->map(fn($studentId) => [
                'student_id'       => $studentId,
                'class_subject_id' => $classSubjectId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ])->all();

        if (empty($grades)) {
            return;
        }

        Grade::upsert($grades, ['student_id', 'class_subject_id']);
    }

    public function getClassGrades($classSubjectId)
    {
        $classSubject = ClassSubject::with(['subject', 'schoolClass'])->findOrFail($classSubjectId);
        $weights = WeightSumScore::where('class_subject_id', $classSubjectId)->first();
        $grades = Grade::with('student.user')->where('class_subject_id', $classSubjectId)->get();
        $totals = $this->getTotals($classSubjectId);
        $grades = $this->calculateFinalScores($grades, $weights, $totals);
        $sortedGrades = $grades->sortByDesc('final_score')->values();

        return [
            'meta' => $classSubject,
            'data' => GradeResource::collection($sortedGrades),
        ];
    }

    public function getStudentDetail($classSubjectId, $studentId)
    {
        $student = Student::with('user')->findOrFail($studentId);
        $subject = ClassSubject::with('subject')->findOrFail($classSubjectId);
        $assignments = Submission::with('post')->where('student_id', $studentId)->whereHas(
            'post',
            fn($q) => $q->where('class_subject_id', $classSubjectId)
        )->get()->map(fn($item) => [
            'id'    => $item->id,
            'score' => $item->score,
            'date'  => $item->created_at,
        ]);

        $exams = ExamAttempt::with('assignment.exam')->where('student_id', $studentId)->whereHas(
            'assignment',
            fn($q) =>
            $q->where('class_subject_id', $classSubjectId)
        )->get()->map(fn($item) => [
            'type'  => $item->assignment->exam->type,
            'score' => $item->score,
            'date'  => $item->started_at,
        ]);

        return [
            'subject'     => $subject->subject->name,
            'student'     => new StudentResource($student),
            'assignments' => $assignments,
            'exams'       => $exams,
        ];
    }

    private function getTotals($classSubjectId)
    {
        $totalAssignment = Post::where('class_subject_id', $classSubjectId)
            ->where('type', 'assignment')->count();
        $exams = ExamAssignment::where('class_subject_id', $classSubjectId)
            ->with('exam:id,type')->get();

        return [
            'assignment' => $totalAssignment,
            'daily'      => $exams->where('exam.type', 'harian')->count(),
            'uts'        => $exams->where('exam.type', 'uts')->count(),
            'uas'        => $exams->where('exam.type', 'uas')->count(),
        ];
    }

    private function calculateFinalScores($grades, $weights, $totals)
    {
        return $grades->map(function ($grade) use ($weights, $totals) {

            $assignmentAvg = $totals['assignment']
                ? $grade->assignment_total_score / $totals['assignment'] : 0;

            $dailyAvg = $totals['daily']
                ? $grade->daily_total_score / $totals['daily'] : 0;

            $grade->final_score = round(
                ($assignmentAvg * $weights->assignment_weight) +
                    ($dailyAvg * $weights->daily_weight) +
                    ($grade->uts_score * $weights->uts_weight) +
                    ($grade->uas_score * $weights->uas_weight),
                2
            );

            $grade->assignment = $assignmentAvg;
            $grade->daily = $dailyAvg;
            $grade->uts = $grade->uts_score;
            $grade->uas = $grade->uts_score;

            return $grade;
        });
    }
}
