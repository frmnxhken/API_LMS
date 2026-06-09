<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'present' => $this->resource->get('present', 0),
            'sick' => $this->resource->get('sick', 0),
            'permission' => $this->resource->get('permission', 0),
            'not_checked_in' => $this->resource->get('not_checked_in', 0),
        ];
    }
}
