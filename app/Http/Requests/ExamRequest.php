<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $examId = $this->route('exam');

        return [
            "title" => "required|unique:exams,title," . $examId,
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'required|integer|min:1',
            'type' => 'required|in:uts,uas,harian',
            'is_random_questions' => 'required|boolean',
            'questions' => 'required|array|min:1',
            'questions.*' => 'integer|exists:questions,id|distinct',
        ];
    }
}
