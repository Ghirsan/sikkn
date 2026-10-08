<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
        'code',
        'name',
        'short_name',
    ];

    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class);
    }
}
