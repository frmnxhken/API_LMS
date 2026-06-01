<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\ExamAssignment;
use App\Models\ExamAttempt;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamAssignmentController extends Controller
{
    public function index($id_class_subject)
    {
        $assignments = ExamAssignment::where('class_subject_id', $id_class_subject)
            ->with('exam')
            ->latest()
            ->get();

        $classSubject = ClassSubject::with('subject')
            ->findOrFail($id_class_subject);

        return response()->json([
            'meta' => [
                'subject' => $classSubject->subject,
            ],
            'data' => $assignments,
        ]);
    }

    public function store(Request $request, $id_class_subject)
    {
        $validated = $request->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ]);

        DB::transaction(function () use ($validated, $id_class_subject) {

            $assignment = ExamAssignment::create([
                'exam_id' => $validated['exam_id'],
                'class_subject_id' => $id_class_subject,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]);

            $this->generateExamAttempts(
                $assignment->id,
                $id_class_subject
            );
        });

        return response()->json([
            'message' => 'Exam assignment created successfully'
        ], 201);
    }

    public function show($id_class_subject, $exam)
    {
        return ExamAssignment::where('class_subject_id', $id_class_subject)
            ->with(['exam.subject'])
            ->findOrFail($exam);
    }

    public function update(Request $request, $id_class_subject, $exam)
    {
        $assignment = ExamAssignment::where('class_subject_id', $id_class_subject)
            ->findOrFail($exam);

        $validated = $request->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ]);

        $assignment->update($validated);

        return response()->json([
            'message' => 'Exam assignment updated successfully'
        ]);
    }

    public function destroy($id_class_subject, $exam)
    {
        $assignment = ExamAssignment::where('class_subject_id', $id_class_subject)
            ->findOrFail($exam);

        $assignment->delete();

        return response()->json([
            'message' => 'Exam assignment deleted successfully'
        ]);
    }

    /**
     * Generate exam attempts for all students in class subject
     */
    protected function generateExamAttempts($assignmentId, $classSubjectId)
    {
        $students = Student::whereHas('enrollments.schoolClass.classSubjects', function ($q) use ($classSubjectId) {
            $q->where('class_subjects.id', $classSubjectId);
        })->get();

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
}
