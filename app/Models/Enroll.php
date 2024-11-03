<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Submission;

class Enroll extends Model
{
    protected $fillable = ['lesson_id', 'user_id'];
    
    public function lessons()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
