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
    public function rules(): array
    {
        $id = $this->route('academic_year')?->id;

        return [
            'start' => [
                'required',
                'integer',
                'digits:4',
                'lte:end',
            ],

            'end' => [
                'required',
                'integer',
                'digits:4',
                'gte:start',
            ],

            Rule::unique('academic_years')->where(
                fn($q) => $q
                    ->where('start', $this->start)
                    ->where('end', $this->end)
            )->ignore($id),
        ];
    }
}
