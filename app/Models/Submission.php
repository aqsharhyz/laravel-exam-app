<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Exam;
use App\Models\Enroll;
use App\Models\Answer;

class Submission extends Model
{
    protected $fillable = ['exam_id', 'enroll_id', 'score'];

    public function exams()
    {
        return $this->belongsTo(Exam::class);
    }

    public function enrolls()
    {
        return $this->belongsTo(Enroll::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
