<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceHistoryResource extends JsonResource
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
            'student_id' => $this->student->id,
            'date' => $this->date,
            'name' => $this->student->user->name,
            'nis' => $this->student->nis,
            'arrival_time' => $this->arrival_time,
            'status' => $this->status,
        ];
    }
}
