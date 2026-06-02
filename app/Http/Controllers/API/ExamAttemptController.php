<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExamQuestionResource;
use App\Models\ExamAssignment;
use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamAttemptController extends Controller
{
    public function index(
        $id_class_subject,
        ExamAssignment $exam
    ) {
        $user = Auth::user();

        return ExamAttempt::where([
            'exam_assignment_id' => $exam->id,
            'student_id' => $user->student->id,
        ])
            ->with([
                'assignment.exam.subject'
            ])
            ->firstOrFail();
    }

    public function store(Request $request, $id_class_subject, ExamAssignment $exam)
    {
        $user = Auth::user();

        $attempt = ExamAttempt::where([
            'exam_assignment_id' => $exam->id,
            'student_id' => $user->student->id,
        ])->first();

        if (!$attempt->started_at) {
            $attempt->update([
                'started_at' => now(),
                'status' => 'in_progress'
            ]);
        }
    }

    public function show(Request $request, $id_class_subject, ExamAssignment $exam)
    {
        $user = Auth::user();


        if ($exam->class_subject_id != $id_class_subject) {
            return response()->json([
                'message' => 'Exam not found'
            ], 404);
        }

        $attempt = ExamAttempt::where([
            'exam_assignment_id' => $exam->id,
            'student_id' => $user->student->id,
        ])->first();

        if (!$attempt) {
            return response()->json([
                'message' => 'You are not assigned to this exam'
            ], 403);
        }

        if (now()->lt($exam->start_time)) {
            return response()->json([
                'message' => 'Exam has not started yet'
            ], 403);
        }

        if (!$attempt->started_at) {
            return response()->json([
                'message' => 'Exam has not started yet'
            ], 403);
        }

        if (now()->gt($exam->end_time)) {
            return response()->json([
                'message' => 'Exam has ended'
            ], 403);
        }

        if ($attempt->status === 'submitted') {
            return response()->json([
                'message' => 'Exam already submitted'
            ], 403);
        }


        $questions = $exam
            ->exam
            ->questions()
            ->with('options')
            ->get();

        return response()->json([
            'attempt_id' => $attempt->id,
            'started_at' => $attempt->started_at,
            'end_time' => $exam->end_time,
            'duration' => $exam->exam->duration,
            'questions' => ExamQuestionResource::collection($questions),
        ]);
    }

    public function attempt(
        Request $request,
        $id_class_subject,
        ExamAssignment $exam
    ) {
        $user = Auth::user();

        $attempt = ExamAttempt::where([
            'exam_assignment_id' => $exam->id,
            'student_id' => $user->student->id,
        ])->firstOrFail();

        if ($attempt->status == 'submitted') {
            return response()->json([
                'message' => 'Exam already submitted'
            ], 403);
        }

        $correctAnswers = 0;

        foreach ($request['answers'] as $answer) {
            $isCorrect = QuestionOption::where([
                'id' => $answer['option_id'],
                'is_correct' => true,
            ])->exists();

            if ($isCorrect) {
                $correctAnswers++;
            }
        }

        $totalQuestions = $exam->exam
            ->questions()
            ->count();

        $score = $totalQuestions > 0
            ? (100 / $totalQuestions) * $correctAnswers
            : 0;

        $attempt->update([
            'score' => round($score, 2),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        return response()->json([
            'score' => round($score, 2),
            'correct_answers' => $correctAnswers,
        ]);
    }
}
