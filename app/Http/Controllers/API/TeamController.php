<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Team;
use Validator;

class TeamController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teams = Team::query();
        if ($search = $request->input('search')) {
            $teams->where('name', 'like', '%' . $search . '%')
            ->orWhere('position', 'like', '%' . $search . '%')
            ->orWhere('message', 'like', '%' . $search . '%');
        }

        if ($sort = $request->input('sort') ?? 'id') {
            $teams->orderBy($sort);
        }

        if ($order = $request->input('order') ?? 'asc') {
            $teams->orderBy('id', $order);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page', 1);

        $result = $teams->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse($result, 'Teams retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        if(isset($input['avatar'])) {
            $upload = $this->uploadImageWithThumbnail($input['avatar'], $input['name'], 'teams/'.$input['name'].'/avatar', 150, 93);
            $input['avatar'] = $upload['image'];
            $input['thumbnail'] = $upload['thumbnail'];
        }
        $team = Team::create($input);
        return $this->sendResponse($team, 'Team created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $team = Team::find($id);
        if($team === null) {
            return $this->sendError('Team not found.');
        }
        return $this->sendResponse($team, 'Team retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $team = Team::find($id);
        if($team === null) {
            return $this->sendError('Team not found.');
        }

        $input = $request->all();
        if(isset($input['avatar'])) {
            if (isset($team->avatar)) {
                $this->removeFiles($team->avatar);
                $this->removeFiles($team->thumbnail);
            }
            $upload = $this->uploadImageWithThumbnail($input['avatar'], $input['name'], 'teams/'.$input['name'].'/avatar', 150, 93);
            $input['avatar'] = $upload['image'];
            $input['thumbnail'] = $upload['thumbnail'];
        }
        $team->update($input);
        return $this->sendResponse($team, 'Team updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $team = Team::find($id);
        if($team === null) {
            return $this->sendError('Team not found.');
        }
        $team->delete();
        return $this->sendResponse($team, 'Team deleted successfully.');
    }

    /**
     * Retrieve all trashed teams.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the trashed teams or an error message.
     */
    public function trash()
    {
        $teams = Team::onlyTrashed()->get();
        return $this->sendResponse($tags, 'Teams retrieved successfully.');
    }

    /**
     * Restore all trashed teams.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored teams or an error message.
     */
    public function restoreAll()
    {
        $teams = Team::onlyTrashed()->restore();
        return $this->sendResponse($teams, 'Teams retrieved successfully.');
    }

    /**
     * Restores a team from the trash by its ID.
     *
     * @param int $id The ID of the team to restore.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored team or an error message.
     */
    public function restore($id)
    {
        $team = Team::onlyTrashed()->find($id);
        if ($team === null) {
            return $this->sendError('Team not found.');
        }

        $team->restore();
        return $this->sendResponse($team, 'Team restored successfully.');
    }

    /**
     * Permanently deletes a team from the trash by its ID.
     *
     * @param int $id The ID of the team to delete.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the deleted team or an error message.
     */
    public function forceDelete($id)
    {
        $team = Team::onlyTrashed()->where('id', $id);
        if ($team === null) {
            return $this->sendError('Team not found.');
        }

        $team->forceDelete();
        return $this->sendResponse($team, 'Team deleted successfully.');
    }
}
