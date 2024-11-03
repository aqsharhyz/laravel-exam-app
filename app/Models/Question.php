<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Exam;
use App\Models\Option;

class Question extends Model
{

    protected $fillable = ['exam_id', 'question_text'];

    public $timestamps = false;

    public function exams()
    {
        return $this->belongsTo(Exam::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    // public function answers()
    // {
    //     return $this->hasMany(Answer::class);
    // }
}
