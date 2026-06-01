<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $guarded = ['id'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function submission_files()
    {
        return $this->hasMany(SubmissionFile::class);
    }
}
