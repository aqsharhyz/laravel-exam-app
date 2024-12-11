<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Question;
use Illuminate\Database\Eloquent\SoftDeletes;

class Option extends Model
{
    use SoftDeletes;

    protected $fillable = ['question_id', 'option_text', 'is_correct'];

    public $timestamps = false;

    public function questions()
    {
        return $this->belongsTo(Question::class);
    }
}
