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

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function questions()
    {
        return $this->belongsToMany(
            Question::class,
            'exam_questions',
            'exam_id',
            'question_id'
        );
    }

    public function assignments()
    {
        return $this->hasMany(ExamAssignment::class);
    }

    public function questionBank()
    {
        return $this->belongsToMany(Question::class, 'exam_questions', 'exam_id', 'question_id');
    }
}
