<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "nis" => $this->nis,
            "name" => $this->user->name,
            "username" => $this->user->username,
            'school_class_id' => $this->enrollments[0]->schoolClass->id,
            "level" => $this->enrollments[0]->schoolClass->level,
            "major" => $this->enrollments[0]->schoolClass->major,
        ];
    }
}
