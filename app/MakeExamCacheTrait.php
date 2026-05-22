<?php

namespace App;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Support\Facades\Cache;

trait MakeExamCacheTrait
{
    function makeExamCache(Exam $exam)
    {
        $question = Question::with(['options' => function ($query) {
            $query->select('id', 'question_id', 'option_text');
        }])
            ->where('exam_id', $exam->id)
            ->select('id', 'question_text')
            ->get();

        Cache::put('exam_' . $exam->id, ['exam' => $exam, 'questions' => $question]);
    }
}
