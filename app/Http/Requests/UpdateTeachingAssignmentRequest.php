<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeachingAssignmentRequest extends FormRequest
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
        $id = $this->route('classSubject')?->id;

        return [
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('class_subjects')
                    ->where(fn($query) => $query->where('school_class_id', $this->school_class_id))
                    ->ignore($id),
            ],

            'teacher_id' => [
                'required',
                'exists:teachers,id',
                Rule::unique('class_subjects')
                    ->where(
                        fn($query) => $query
                            ->where('school_class_id', $this->school_class_id)
                            ->where('subject_id', $this->subject_id)
                    )
                    ->ignore($id),
            ],
        ];
    }
}
