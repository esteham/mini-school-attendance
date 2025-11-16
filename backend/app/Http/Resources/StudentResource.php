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
            'id'         => $this->id,
            'name'       => $this->name,
            'student_id' => $this->student_id,
            'class'      => $this->class,
            'section'    => $this->section,
            'photo'      => $this->photo ? asset('storage/' . $this->photo) : null,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
