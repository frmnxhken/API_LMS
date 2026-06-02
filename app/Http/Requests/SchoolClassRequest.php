<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolClassRequest extends FormRequest
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
        $schoolClass = $this->route('schoolClass');

        return [
            'level' => ['required'],
            'major' => ['required'],
            'section' => [
                'required',
                Rule::unique('school_classes')->ignore($schoolClass?->id)
                    ->where(
                        fn($query) => $query
                            ->where('level', $this->level)
                            ->where('major', $this->major)
                    ),
            ],
        ];
    }
}
