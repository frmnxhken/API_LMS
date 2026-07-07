<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Services\ExamAttemptService;
use Illuminate\Http\Request;

class ExamAttemptController extends Controller
{
    public function __construct(private ExamAttemptService $service) {}

    public function index($id_class_subject, Exam $exam)
    {
        $attempt = $this->service->getAttempt($exam);
        return response()->json($attempt);
    }

    public function store(Request $request, $id_class_subject, Exam $exam)
    {
        $start = $this->service->startAttempt($exam);
        return response()->json($start);
    }

    public function show($id_class_subject, Exam $exam)
    {
        $examSession = $this->service->getExamSession($exam);
        return response()->json($examSession);
    }

    public function attempt(Request $request, $id_class_subject, Exam $exam)
    {
        $submit = $this->service->submitAttempt($exam, $request->answers);
        return response()->json($submit);
    }
}
