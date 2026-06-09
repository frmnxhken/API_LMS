<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAssignment;

class ExamAssignmentService
{
    public function create($data, $classSubjectId)
    {
        $exam = Exam::findOrFail($data['exam_id']);

        if (in_array(strtolower($exam->type), ['uts', 'uas'])) {
            $exists = ExamAssignment::where('exam_id', $exam->id)
                ->where('class_subject_id', $classSubjectId)->exists();

            if ($exists) {
                abort(422, 'Exam UTS/UAS sudah pernah dibuat untuk kelas ini');
            }
        }

        return ExamAssignment::create([
            'exam_id' => $data['exam_id'],
            'class_subject_id' => $classSubjectId,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
        ]);
    }
}
