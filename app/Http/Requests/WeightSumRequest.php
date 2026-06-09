<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WeightSumRequest extends FormRequest
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
        return [
            'assignment_weight' => 'required|numeric|min:0|max:100',
            'daily_weight' => 'required|numeric|min:0|max:100',
            'uts_weight' => 'required|numeric|min:0|max:100',
            'uas_weight' => 'required|numeric|min:0|max:100',
        ];
    }
}
