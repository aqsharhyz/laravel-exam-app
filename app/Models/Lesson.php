<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Exam;
use App\Models\Enroll;

class Lesson extends Model
{
    protected $fillable = ['title', 'description', 'is_active', 'visibility'];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function enrolls()
    {
        return $this->hasMany(Enroll::class);
    }
}
