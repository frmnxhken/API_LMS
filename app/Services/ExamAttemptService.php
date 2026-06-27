<?php

namespace App\Services;

use App\Http\Resources\ExamQuestionResource;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\QuestionOption;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class ExamAttemptService
{
    public function generate($assignmentId, $classSubjectId)
    {
        $students = Student::whereHas(
            'enrollments.schoolClass.classSubjects',
            function ($q) use ($classSubjectId) {
                $q->where('class_subjects.id', $classSubjectId);
            }
        )->get();

        $data = $students->map(function ($student) use ($assignmentId) {
            return [
                'exam_assignment_id' => $assignmentId,
                'student_id' => $student->id,
                'score' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        ExamAttempt::insert($data);
    }

    public function rollbackFromAttempts($assignment, $examType)
    {
        $attempts = ExamAttempt::where('exam_assignment_id', $assignment->id)->get();

        foreach ($attempts as $attempt) {
            $grade = Grade::firstOrCreate([
                'student_id' => $attempt->student_id,
                'class_subject_id' => $assignment->class_subject_id,
            ]);

            $score = $attempt->score ?? 0;

            switch ($examType) {
                case 'uas':
                    $grade->update(['uas_score' => 0]);
                    break;
                case 'uts':
                    $grade->update(['uts_score' => 0]);
                    break;
                case 'harian':
                    $grade->decrement('daily_total_score', $score);
                    break;
            }
        }
    }

    public function getAttempt($exam)
    {
        $user = Auth::user();
        $attempt = ExamAttempt::where([
            'exam_assignment_id' => $exam->id,
            'student_id' => $user->student->id,
        ])->with('assignment.exam.subject')->firstOrFail();

        return $attempt;
    }

    public function startAttempt($exam)
    {
        $this->validateExamWindow($exam);
        $attempt = $this->getAttempt($exam);

        if (!$attempt->started_at) {
            $attempt->update([
                'started_at' => now(),
                'status'     => 'in_progress'
            ]);
        }

        return $attempt;
    }

    public function getExamSession($exam)
    {
        $this->validateExamWindow($exam);
        $attempt = $this->getAttempt($exam);

        $this->validateAttempt($attempt);
        $questions = $exam->exam->questions()->with('options')->get();

        $data =  [
            'attempt_id' => $attempt->id,
            'started_at' => $attempt->started_at,
            'end_time'   => $exam->end_time,
            'duration'   => $exam->exam->duration,
            'type'       => $exam->exam->type,
            'subject'    => $exam->exam->subject->name,
            'questions'  => ExamQuestionResource::collection($questions),
        ];

        return $data;
    }

    public function submitAttempt($exam, $answers)
    {
        $user = Auth::user();
        $attempt = $this->getAttempt($exam);

        if ($attempt->status === 'submitted') {
            abort(403, 'Already submitted');
        }

        $correct = $this->calculateScore($answers);
        $total = $exam->exam->questions()->count();

        $score = $total > 0 ? (100 / $total) * $correct : 0;

        $attempt->update([
            'score'        => round($score, 2),
            'submitted_at' => now(),
            'status'       => 'submitted',
        ]);

        $this->updateGrade($exam, $user, $score);

        return [
            'score'           => round($score, 2),
            'correct_answers' => $correct,
        ];
    }

    private function calculateScore($answers)
    {
        $correct = 0;

        foreach ($answers as $answer) {
            $isCorrect = QuestionOption::where([
                'id' => $answer['option_id'],
                'is_correct' => true,
            ])->exists();

            if ($isCorrect) $correct++;
        }

        return $correct;
    }

    private function updateGrade($exam, $user, $score)
    {
        $grade = Grade::where('student_id', $user->student->id)->firstOrFail();

        match ($exam->exam->type) {
            'uas' => $grade->update(['uas_score' => $score]),
            'uts' => $grade->update(['uts_score' => $score]),
            'harian' => $grade->update([
                'daily_total_score' => $grade->daily_total_score + $score
            ]),
        };
    }

    private function validateExamWindow($exam): void
    {
        if (now()->lt($exam->start_time)) abort(403, 'Belum dimulai');
        if (now()->gt($exam->end_time)) abort(403, 'Berakhir');
    }

    private function validateAttempt($attempt): void
    {
        if (!$attempt->started_at) abort(403, 'Not started');
        if ($attempt->status === 'submitted') abort(403, 'Already submitted');
    }
}
