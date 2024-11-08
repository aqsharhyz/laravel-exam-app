<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Submission;
use App\Models\Lesson;
use App\Models\Question;

class Exam extends Model
{

    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'duration',
        'total_score',
        'passing_grade',
    ];

    public function lessons()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
