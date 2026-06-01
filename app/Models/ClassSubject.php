<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::addGlobalScope('activeAcademicYear', function (Builder $builder) {
            $builder->whereHas('academicYear', function ($q) {
                $q->where('is_active', 1);
            });
        });
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function students()
    {
        return $this->belongsToMany(
            User::class,
            'class_subject_students',
            'class_subject_id',
            'student_id'
        );
    }
}
