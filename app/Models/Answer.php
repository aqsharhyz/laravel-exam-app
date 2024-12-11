<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Option;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use SoftDeletes;

    protected $fillable = ['question_id', 'submission_id', 'selected_option_id'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function submissions()
    {
        return $this->belongsTo(Submission::class);
    }

    public function options()
    {
        return $this->belongsTo(Option::class);
    }
}
