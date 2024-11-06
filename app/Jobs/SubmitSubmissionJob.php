<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Submission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SubmitSubmissionJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $examId, public string $submissionId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $submission = Submission::with('answers')->find($this->submissionId);

        if ($submission->is_submitted) {
            return;
        }

        $questions = Question::with(['options' => function ($query) {
            $query->select('id', 'question_id')->where('is_correct', true);
        }])
            ->where('exam_id', $this->examId)
            ->select('id')
            ->get();

        $score = 0;
        foreach ($questions as $question) {
            $answer = $submission->answers->where('question_id', $question->id)->first();
            if ($answer && $answer->selected_option_id == $question->options->first()->id) {
                $score++;
            }
        }

        $exam = Exam::find($this->examId);
        $score = ($score / count($questions)) * $exam->total_score;
        $submission->score = $score;
        $submission->is_submitted = true;
        $submission->save();
    }
}
