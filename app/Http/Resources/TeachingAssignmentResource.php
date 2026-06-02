<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeachingAssignmentResource extends JsonResource
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
            "level" => $this->schoolClass->level,
            "major" => $this->schoolClass->major,
            "section" => $this->schoolClass->section,
            "subject" => $this->subject->name,
            "nip" => $this->teacher->nip,
            "teacher" => $this->teacher->user->name,
        ];
    }
}
