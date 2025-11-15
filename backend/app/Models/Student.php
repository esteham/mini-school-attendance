<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;

    use HasFactory;

    protected $fillable = [
        'name',
        'student_id',
        'class',
        'section',
        'photo',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
