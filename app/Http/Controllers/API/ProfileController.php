<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rules\Password;


class ProfileController extends BaseController
{
    /**
     * Retrieve the user's profile.
     *
     * @return mixed Returns the response after retrieving the user profile.
     */
    public function index()
    {
        $user = auth()->user();
        return $this->sendResponse($user, 'Profile retrieved successfully.');
    }

    /**
     * Update the user's profile with the provided request data.
     *
     * @param Request $request The request object containing the updated user information.
     * @return mixed Returns the response after updating the user profile.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = auth()->user();
        $user->update($request->all());
        return $this->sendResponse($user, 'Profile updated successfully.');
    }

    /**
     * Avatar change function.
     *
     * @param Request $request The request object containing the avatar file.
     * @return mixed Returns the response after updating the avatar.
     */
    public function avatarChange(Request $request)
    {
        $user = auth()->user();
        if ($request->hasFile('avatar')) {
            if ($user->avatar && $user->thumbnail) {
                \Storage::delete('public/'.$user->avatar);
                \Storage::delete('public/'.$user->thumbnail);
            }
            $avatar = $request->file('avatar');
            $originalName = $avatar->getClientOriginalName();
            $ext = $avatar->getClientOriginalExtension();

            $filename = time() . '-avatar' . '.' . $ext;
            $fileThumbnail = time() . '-avatar-thumbnail' . '.' . $ext;

            $path = $request->file('avatar')->storeAs('avatars', $filename, 'public');
            $pathThumbnail = $request->file('avatar')->storeAs('avatars/thumbnail', $fileThumbnail, 'public');

            $smallthumbnailpath = public_path('storage/avatars/thumbnail/'.$fileThumbnail);
            $this->createThumbnail($smallthumbnailpath, 150, 93);

            $user->avatar = $path;
            $user->thumbnail = $pathThumbnail;
        }
        $user->save();
        return $this->sendResponse($user, 'Avatar updated successfully.');
    }

    /**
     * Method to change the password for the user.
     *
     * @param Request $request The request object containing the new password.
     * @return mixed Returns the response after updating the password.
     */
    public function passwordChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if($user = auth()->user()){
            $user->password = bcrypt($request->password);
            $user->save();
            return $this->sendResponse($user, 'Password updated successfully.');
        }
    }
}
