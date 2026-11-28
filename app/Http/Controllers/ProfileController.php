<?php

namespace App\Http\Controllers;

use App\Support\Media;
use App\Http\Requests\ProfileUpdateRequest;
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
        $user = $request->user();
        $data = $request->validated();

        // Normalize newsletter toggle
        $data['newsletter_opt_in'] = $request->boolean('newsletter_opt_in');
        $data['cart_reminders'] = $request->boolean('cart_reminders');

        // Keep legacy address column in sync for existing flows
        if (!empty($data['address_line1'])) {
            $data['address'] = $data['address_line1'];
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            try {
                $avatarPath = Media::store($request->file('avatar'), 'avatars');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Avatar upload failed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

                return back()->withInput()->withErrors(['avatar' => __('profile.avatar_upload_failed')]);
            }
            Media::delete($user->avatar);

            $data['avatar'] = $avatarPath;
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

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

        return Redirect::route('home');
    }
}
