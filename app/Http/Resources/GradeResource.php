<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->student->id,
            "nis" => $this->student->nis,
            "name" => $this->student->user->name,
            "uas" => $this->uas,
            "uts" => $this->uts,
            "assignment" => $this->assignment,
            "daily" => $this->daily,
            "final_score" => $this->final_score,
        ];
    }
}
