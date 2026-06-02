<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
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
        $teacher = $this->route('teacher');

        return [
            'nip' => [
                'required',
                Rule::unique('teachers', 'nip')
                    ->ignore($teacher?->id)
            ],
            'name' => ['required'],
            'phone' => ['required', 'max:13'],
            'password' => ['nullable', 'min:6'],
        ];
    }
}
