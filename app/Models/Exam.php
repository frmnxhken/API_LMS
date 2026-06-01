<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $guarded = ["id"];

    public function teacher()
    {
        return $this->belongsTo(User::class, "teacher_id");
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function assignments()
    {
        return $this->hasMany(ExamAssignment::class);
    }
}
