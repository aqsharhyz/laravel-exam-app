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
use Illuminate\Support\Facades\Storage;
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

    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image', //! mime and maxsize
        ]);

        if ($request->file()) {
            $fileName = time() . '_' . $request->profile_picture->getClientOriginalName();
            $filePath = $request->file('profile_picture')->storeAs('profile_pictures', $fileName, 'public');

            $user = $request->user();
            //! remove old profile picture
            // echo Storage::exists($user->profile_picture);
            // echo json_encode(Storage::files('profile_pictures'));
            // return;
            // if (!empty($user->profile_picture) && Storage::exists($user->profile_picture)) {
            //     Storage::delete($user->profile_picture);
            // }
            $user->profile_picture = $filePath;
            $user->save();

            return back()->with('status', 'profile-picture-uploaded');
        }

        return back()->with('status', 'profile-picture-upload-failed');
    }

    public function deleteProfilePicture(Request $request): RedirectResponse
    {
        $request->user()->deleteProfilePicture();

        return Redirect::route('profile.edit')->with('status', 'profile-picture-deleted');
    }
}
