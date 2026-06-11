<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Attendance extends Model
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

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
