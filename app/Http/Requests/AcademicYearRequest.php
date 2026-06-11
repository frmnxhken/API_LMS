<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicYearRequest extends FormRequest
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
    public function rules()
    {
        $id = $this->route('academicYear')?->id;

        return [
            'start' => [
                'required',
                'date',
                Rule::unique('academic_years', 'start')->where(function ($query) {
                    return $query->whereYear('start', date('Y', strtotime($this->start)));
                })->ignore($id),
            ],
            'end' => [
                'required',
                'date',
                'after:start',
            ],
        ];
    }
}
