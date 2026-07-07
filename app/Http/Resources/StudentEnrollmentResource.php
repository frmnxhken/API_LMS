<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentEnrollmentResource extends JsonResource
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
            "photo" => $this->user->photo,
            'school_class_id' => $this->enrollments[0]->schoolClass->id,
            "level" => $this->enrollments[0]->schoolClass->level,
            "major" => $this->enrollments[0]->schoolClass->major,
            "section" => $this->enrollments[0]->schoolClass->section,
        ];
    }
}
