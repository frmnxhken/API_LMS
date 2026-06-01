<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = ['id'];

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function post_files()
    {
        return $this->hasMany(PostFile::class, "post_id");
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, "post_id");
    }
}
