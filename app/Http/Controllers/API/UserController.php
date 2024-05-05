<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::query();
        if ($search = $request->input('search')) {
            $users->where('name', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%');
        }

        if ($sort = $request->input('sort') ?? 'id') {
            $users->orderBy($sort);
        }

        if ($order = $request->input('order') ?? 'asc') {
            $users->orderBy('id', $order);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page', 1);

        $result = $users->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse($result, 'Users retrieved successfully.');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email,NULL,id,deleted_at,NULL'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'avatar' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role ?? 'user',
        ]);

        if ($request->hasFile('avatar')) {

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

            $user->save();
        }

        return $this->sendResponse($user, 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        if ($user === null) {
            return $this->sendError('User not found.');
        }
        return $this->sendResponse($user, 'User retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id . ',id,deleted_at,NULL',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($id);
        if($user === null) {
            return $this->sendError('User not found.');
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Storage::exists('public/'.$user->avatar)) {
                \Storage::delete('public/'.$user->avatar);
            }

            if ($user->thumbnail && \Storage::exists('public/'.$user->thumbnail)) {
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
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return $this->sendResponse($user, 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if($user === null) {
            return $this->sendError('User not found.');
        }
        $user->delete();
        return $this->sendResponse($user, 'User deleted successfully.');
    }

    /**
     * Retrieves the trashed users from the database.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the trashed users.
     */
    public function trash()
    {
        $users = User::onlyTrashed()->get();
        return $this->sendResponse($users, 'Users retrieved successfully.');
    }

    /**
     * Retrieves the trashed users from the database.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the trashed users.
     */
    public function restoreAll()
    {
        $users = User::onlyTrashed()->restore();
        return $this->sendResponse($users, 'Users restored successfully.');
    }

    /**
     * Restores a user from the trash.
     *
     * @param string $id The ID of the user to restore.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored user.
     */
    public function restore(string $id)
    {
        $user = User::onlyTrashed()->where('id', $id)->restore();
        if ($user === null) {
            return $this->sendError('User not found.');
        }
        return $this->sendResponse($user, 'User restored successfully.');
    }

    /**
     * Permanently deletes a user from the trash.
     *
     * @param string $id The ID of the user to delete permanently.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the deleted user.
     */
    public function forceDelete(string $id)
    {
        $user = User::onlyTrashed()->find($id);
        if ($user === null) {
            return $this->sendError('User not found.');
        }

        if($user->avatar && \Storage::exists('public/'.$user->avatar)) {
            \Storage::delete('public/'.$user->avatar);
        }
        if($user->thumbnail && \Storage::exists('public/'.$user->thumbnail)) {
            \Storage::delete('public/'.$user->thumbnail);
        }
        $user->forceDelete();
        return $this->sendResponse($user, 'User deleted permanently successfully.');
    }

}
