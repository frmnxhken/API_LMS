<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassSubjectResource extends JsonResource
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
            'class_name' => $this->schoolClass->level . " " . $this->schoolClass->major . "" . $this->schoolClass->section,
            'subject' => $this->subject->name,
            'teacher' => $this->teacher->user->name,
            'academic_year' => $this?->academicYear,
        ];
    }
}
