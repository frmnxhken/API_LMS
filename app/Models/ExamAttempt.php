<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $guarded = ["id"];

    public function assignment()
    {
        return $this->belongsTo(
            ExamAssignment::class,
            'exam_assignment_id'
        );
    }

    public function student()
    {
        return $this->belongsTo(
            User::class,
            'student_id'
        );
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
