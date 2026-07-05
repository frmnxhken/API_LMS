<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'uts_score' => 'float',
        'uas_score' => 'float',
        'daily_total_score' => 'float',
        'assignment_total_score' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class);
    }
}
