<?php

namespace App\Exports;

use App\Models\Grade;
use App\Models\Post;
use App\Models\ExamAssignment;
use App\Models\WeightSumScore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GradeExport implements FromCollection, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(
        private int $classSubjectId
    ) {}

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4F81BD']],
            ],
        ];
    }

    public function collection()
    {
        $weights = WeightSumScore::where('class_subject_id', $this->classSubjectId)->first();

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
                    ($assignment * $weights['assignment_weight']) +
                    ($daily * $weights['daily_weight']) +
                    ($uts * $weights['uts_weight']) +
                    ($uas * $weights['uas_weight']);

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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();
                $range = "A1:{$lastCol}{$lastRow}";
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => false,
                    ],
                ]);
            },
        ];
    }
}
