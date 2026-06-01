<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAssignment extends Model
{
    protected $guarded = ["id"];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }
}
