<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            "subject" => $this->subject->name,
            "class_name" => $this->schoolClass->level . " " . $this->schoolClass->major . " " . $this->schoolClass->section,
            "posts" => $this->posts
        ];
    }
}
