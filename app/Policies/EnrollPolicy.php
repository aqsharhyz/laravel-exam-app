<?php

namespace App\Policies;

use App\Models\Enroll;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EnrollPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
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
    public function view(User $user, Enroll $enroll): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function enroll(User $user, Lesson $lesson): Response
    {
        if ($lesson->is_active && $lesson->visibility === 'public') {
            return Response::allow();
        }

        // if ($lesson->is_active && $user->isAdministrator()) {
        //     return Response::allow();
        // }

        return Response::deny('You cannot enroll in this lesson');
    }

    public function unenroll() {}

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Enroll $enroll): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Enroll $enroll): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Enroll $enroll): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Enroll $enroll): bool
    {
        return false;
    }
}
