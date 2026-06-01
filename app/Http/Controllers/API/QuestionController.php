<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function index(Exam $exam)
    {
        return response()->json([
            'questions' => $exam->questions()
                ->with('options')
                ->get()
        ]);
    }

    public function store(Request $request, Exam $exam)
    {
        DB::transaction(function () use ($request, $exam) {
            foreach ($request->questions as $questionData) {
                $question = $exam->questions()->create([
                    'question' => $questionData['question']
                ]);

                foreach (
                    $questionData['options']
                    as $optionData
                ) {
                    $question->options()->create([
                        'option' => $optionData['option'],
                        'is_correct' => $optionData['is_correct'],
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Questions created'
        ]);
    }

    public function update(Request $request, Question $question)
    {
        DB::transaction(function () use ($request, $question) {

            $question->update([
                'question' => $request->question
            ]);

            $existingOptionIds = [];

            foreach ($request->options as $optionData) {

                if (isset($optionData['id'])) {

                    $option = $question
                        ->options()
                        ->findOrFail($optionData['id']);

                    $option->update([
                        'option' => $optionData['option'],
                        'is_correct' => $optionData['is_correct'],
                    ]);

                    $existingOptionIds[] = $option->id;
                } else {

                    $option = $question->options()->create([
                        'option' => $optionData['option'],
                        'is_correct' => $optionData['is_correct'],
                    ]);

                    $existingOptionIds[] = $option->id;
                }
            }

            $question->options()
                ->whereNotIn('id', $existingOptionIds)
                ->delete();
        });

        return response()->json([
            'message' => 'Question updated'
        ]);
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json([
            'message' => 'Question deleted'
        ]);
    }
}
