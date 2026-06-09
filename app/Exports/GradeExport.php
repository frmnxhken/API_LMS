<?php

namespace App\Exports;

use App\Models\Grade;
use App\Models\Post;
use App\Models\ExamAssignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GradeExport implements FromCollection, WithHeadings
{
    public function __construct(
        private int $classSubjectId
    ) {}

    public function collection()
    {
        $weights = [
            'assignment' => 0.2,
            'daily' => 0.2,
            'uts' => 0.3,
            'uas' => 0.3,
        ];

        $totalAssignment = Post::where(
            'class_subject_id',
            $this->classSubjectId
        )
            ->where('type', 'assignment')
            ->count();

        $exams = ExamAssignment::where(
            'class_subject_id',
            $this->classSubjectId
        )
            ->with('exam:id,type')
            ->get();

        $totalDaily = $exams
            ->where('exam.type', 'harian')
            ->count();

        $totalUTS = $exams
            ->where('exam.type', 'uts')
            ->count();

        $totalUAS = $exams
            ->where('exam.type', 'uas')
            ->count();

        return Grade::with('student.user')
            ->where('class_subject_id', $this->classSubjectId)
            ->get()
            ->map(function ($grade, $index) use (
                $weights,
                $totalAssignment,
                $totalDaily,
                $totalUTS,
                $totalUAS
            ) {

                $assignment = $totalAssignment > 0
                    ? $grade->assignment_total_score / $totalAssignment
                    : 0;

                $daily = $totalDaily > 0
                    ? $grade->daily_total_score / $totalDaily
                    : 0;

                $uts = $totalUTS > 0
                    ? $grade->uts_score
                    : 0;

                $uas = $totalUAS > 0
                    ? $grade->uas_score
                    : 0;

                $final =
                    ($assignment * $weights['assignment']) +
                    ($daily * $weights['daily']) +
                    ($uts * $weights['uts']) +
                    ($uas * $weights['uas']);

                return [
                    'No' => $index + 1,
                    'Nama' => $grade->student->user->name,
                    'NIS' => $grade->student->nis,
                    'Tugas' => round($assignment, 2),
                    'Harian' => round($daily, 2),
                    'UTS' => round($uts, 2),
                    'UAS' => round($uas, 2),
                    'Nilai Akhir' => round($final, 2),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NIS',
            'Tugas',
            'Harian',
            'UTS',
            'UAS',
            'Nilai Akhir',
        ];
    }
}
