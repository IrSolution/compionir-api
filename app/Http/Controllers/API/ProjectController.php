<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Project;
use Validator;

class ProjectController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = Project::query();
        if ($search = $request->input('search')) {
            $projects->where('project_name', 'like', '%' . $search . '%')
            ->orWhere('description', 'like', '%' . $search . '%');
        }

        if ($sort = $request->input('sort') ?? 'id') {
            $projects->orderBy($sort);
        }

        if ($order = $request->input('order') ?? 'asc') {
            $projects->orderBy('id', $order);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page', 1);

        $result = $projects->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse($result, 'Projects retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'images' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $images = array();
        $thumbnails = array();
        if(isset($input['images'])) {
            foreach ($input['images'] as $image) {
                $result = $this->uploadImageWithThumbnail($image, $input['project_name'], 'projects/'.$input['project_name'].'/images', 150, 93);
                $images[] = $result['image'];
                $thumbnails[] = $result['thumbnail'];
            }
        }
        $input['images'] = implode(',', $images);
        $input['thumbnail'] = implode(',', $thumbnails);

        $project = Project::create($input);
        return $this->sendResponse($project, 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::find($id);
        if(is_null($project)) {
            return $this->sendError('Project not found.');
        }
        return $this->sendResponse($project, 'Project retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        if($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $project = Project::find($id);
        if(is_null($project)) {
            return $this->sendError('Project not found.');
        }

        $input = $request->all();
        $images = array();
        $thumbnails = array();
        if(isset($input['images'])) {
            foreach (explode(',', $project->images) as $image) {
                $this->removeFiles($image);
            }

            foreach (explode(',', $project->thumbnail) as $thumb) {
                $this->removeFiles($thumb);
            }

            foreach ($input['images'] as $image) {
                $result = $this->uploadImageWithThumbnail($image, $input['project_name'], 'projects/'.$input['project_name'].'/images', 150, 93);
                $images[] = $result['image'];
                $thumbnails[] = $result['thumbnail'];
            }

            $input['images'] = implode(',', $images);
            $input['thumbnail'] = implode(',', $thumbnails);
        }
        $project->update($input);


        return $this->sendResponse($project, 'Project created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::find($id);
        if(is_null($project)) {
            return $this->sendError('Project not found.');
        }
        $project->delete();
        return $this->sendResponse($project, 'Project deleted successfully.');
    }

    /**
     * Retrieves all soft-deleted projects.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the retrieved projects and a success message.
     */
    public function trash()
    {
        $projects = Project::onlyTrashed()->get();
        return $this->sendResponse($projects, 'Projects retrieved successfully.');
    }

    /**
     * Restores all soft-deleted projects.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored projects or an error message if no projects are found.
     */
    public function restoreAll()
    {
        $projects = Project::onlyTrashed()->restore();
        return $this->sendResponse($projects, 'Projects retrieved successfully.');
    }

    /**
     * Restores a specific trashed project.
     *
     * @param int $id The ID of the project to restore.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored project or an error message if the project is not found.
     */
    public function restore($id)
    {
        $project = Project::onlyTrashed()->find($id);
        if(is_null($project)) {
            return $this->sendError('Project not found.');
        }
        $project->restore();
        return $this->sendResponse($project, 'Project restored successfully.');
    }

    /**
     * Permanently deletes a project from the trash.
     *
     * @param int $id The ID of the project to delete permanently.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the deleted project or an error message if the project is not found.
     */
    public function forceDelete($id)
    {
        $project = Project::onlyTrashed()->find($id);
        if(is_null($project)) {
            return $this->sendError('Project not found.');
        }
        if(!empty($project->images)) {
            foreach (explode(',', $project->images) as $image) {
                $this->removeFiles($image);
            }
        }
        if(!empty($project->thumbnail)) {
            foreach (explode(',', $project->thumbnail) as $thumb) {
                $this->removeFiles($thumb);
            }
        }
        $project->forceDelete();
        return $this->sendResponse($project, 'Project deleted successfully.');
    }

}
