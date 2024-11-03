<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Enroll;
use App\Models\Lesson;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function showReport(): View
    {
        return view('dashboard');
    }

    public function reportData(): JsonResponse
    {
        $total_lessons = Enroll::where('user_id', Auth::id())->count();
        $total_exams_submission = Submission::whereIn('enroll_id', function ($query) {
            $query->select('id')
                ->from('enrolls')
                ->where('user_id', Auth::id());
        })->count();
        $total_passed_exams = 0; //!
        $total_completed_lessons = 0; //!
        $average_score = 0; //!
        return response()->json([
            'data' => [
                'total_lessons' => $total_lessons,
                'total_exams_submission' => $total_exams_submission,
            ],
        ]);
    }
}
