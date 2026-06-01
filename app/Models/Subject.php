<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $guarded = ['id'];

    public function class_subjects()
    {
        return $this->hasMany(ClassSubject::class);
    }
}
