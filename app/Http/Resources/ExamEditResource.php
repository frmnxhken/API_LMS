<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamEditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'source' => $this->questionBank?->name,
            'options' => $this->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'option' => $option->option,
                    'is_correct' => $option->is_correct,
                ];
            }),
        ];
    }
}
