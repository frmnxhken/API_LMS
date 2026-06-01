<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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

        $type = $this->getPostType();

        return [
            "title" => "required|string",
            "content" => "required|string",
            "due" => [
                $type === "assignment"
                    ? "required"
                    : "nullable",
                "date"
            ],
            "files" => "nullable|array",
            "files.*" => "file|max:20480",
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            "type" => $this->getPostType()
        ]);
    }

    protected function getPostType()
    {
        return $this->segment(4);
    }
}
