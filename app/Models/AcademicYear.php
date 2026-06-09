<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $guarded = ['id'];

    protected $attributes = [
        'is_active' => 0,
    ];

    public static function active()
    {
        return self::where('is_active', true)->firstOrFail();
    }

    public function class_subjects()
    {
        return $this->hasMany(ClassSubject::class);
    }

    public static function activeId()
    {
        return self::where('is_active', 1)->value('id');
    }
}
