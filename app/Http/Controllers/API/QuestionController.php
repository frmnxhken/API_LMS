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
        $meta = $exam->load("subject");
        $questions =  $exam->questions()->with("options")->get();

        return response()->json([
            "meta"      => $meta,
            "questions" => $questions
        ]);
    }

    public function store(Request $request, Exam $exam)
    {
        DB::transaction(function () use ($request, $exam) {
            foreach ($request->questions as $questionData) {
                $question = $exam->questions()->create([
                    "question" => $questionData["question"]
                ]);

                $question->options()->createMany(
                    array_map(fn($opt) => [
                        "option"     => $opt["option"],
                        "is_correct" => $opt["is_correct"],
                    ], $questionData["options"])
                );
            }
        });

        return response()->json(["message" => "success"]);
    }

    public function update(Request $request, Question $question)
    {
        DB::transaction(function () use ($request, $question) {

            $question->update(["question" => $request->question]);

            $existingOptionIds = [];

            foreach ($request->options as $optionData) {
                if (isset($optionData["id"])) {
                    $option = $question->options()->findOrFail($optionData["id"]);
                    $option->update([
                        "option"     => $optionData["option"],
                        "is_correct" => $optionData["is_correct"],
                    ]);

                    $existingOptionIds[] = $option->id;
                } else {
                    $option = $question->options()->create([
                        "option"     => $optionData["option"],
                        "is_correct" => $optionData["is_correct"],
                    ]);

                    $existingOptionIds[] = $option->id;
                }
            }

            $question->options()->whereNotIn("id", $existingOptionIds)->delete();
        });

        return response()->json(["message" => "success"]);
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return response()->json(["message" => "success"]);
    }
}
