<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceTodayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attendance = $this->attendances->first();

        return [
            'id' => $this->id,
            'student_id' => $this->user->student->id,
            'date' => $attendance?->date,
            'name' => $this->user?->name,
            'nis' => $this->nis,
            'arrival_time' => $attendance?->arrival_time,
            'status' => $attendance?->status,
        ];
    }
}
