<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SubmissionPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Submission $submission): bool
    {
        //!
        return $user->enrolls->contains($submission->exam->lesson_id) && $submission->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //!
        return false;
    }

    public function attempt(User $user, Submission $submission, $lessonId, $enrolledId, $examId): bool | Response
    {
        $submitted = Submission::where('enroll_id', $enrolledId)
            ->where('exam_id', $examId)
            ->where('is_submitted', true)
            ->get();

        if ($submitted->isNotEmpty()) {
            return redirect()->route('submissions.show', [
                'lessonId' => $lessonId,
                'examId' => $examId,
                'submissionId' => $submitted->last()->id
            ]);
        }

        $submission = Submission::where('enroll_id', $enrolledId)
            ->where('exam_id', $examId)
            ->where('is_submitted', false)
            ->get();

        $examDuration = Exam::where('id', $examId)->select('duration')->first()->duration;

        if ($submission->last()->created_at->diffInSeconds(now()) > $examDuration) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Submission $submission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Submission $submission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Submission $submission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Submission $submission): bool
    {
        return false;
    }
}
