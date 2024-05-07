<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Tag;
use Validator;

class TagController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tags = Tag::query();
        if ($search = $request->input('search')) {
            $tags->where('title', 'like', '%' . $search . '%');
        }

        if ($sort = $request->input('sort') ?? 'id') {
            $tags->orderBy($sort);
        }

        if ($order = $request->input('order') ?? 'asc') {
            $tags->orderBy('id', $order);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page', 1);

        $result = $tags->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse($result, 'Tags retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:tags,title,NULL,id,deleted_at,NULL',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $tag = Tag::create($request->all());
        return $this->sendResponse($tag, 'Tag created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tag = Tag::find($id);
        if(is_null($tag)) {
            return $this->sendError('Tag not found.');
        }
        return $this->sendResponse($tag, 'Tag retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:tags,title,' . $id . ',id,deleted_at,NULL',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $tag = Tag::find($id);
        if(is_null($tag)) {
            return $this->sendError('Tag not found.');
        }
        $tag->update($request->all());

        return $this->sendResponse($tag, 'Tag updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tag = Tag::find($id);
        if(is_null($tag)) {
            return $this->sendError('Tag not found.');
        }
        $tag->delete();
        return $this->sendResponse($tag, 'Tag deleted successfully.');
    }

    /**
     * Retrieves the tags based on the search criteria from the request.
     *
     * @param Request $request The request object containing the search criteria.
     * @return Some_Return_Value The response containing the retrieved tags.
     */
    public function getTags(Request $request)
    {
        $tags = Tag::query();
        if ($search = $request->input('search')) {
            $tags->where('title', 'like', '%' . $search . '%');
        }
        $tags = $tags->get();
        return $this->sendResponse($tags, 'Tags retrieved successfully.');
    }

    /**
     * Retrieves all soft-deleted tags.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the retrieved tags.
     */
    public function trash()
    {
        $tags = Tag::onlyTrashed()->get();
        return $this->sendResponse($tags, 'Tags retrieved successfully.');
    }

    /**
     * Restore all soft-deleted items.
     *
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
    public function restoreAll()
    {
        $tags = Tag::onlyTrashed()->restore();
        return $this->sendResponse($tags, 'Tags retrieved successfully.');
    }

    /**
     * Restore a specific resource by ID from the soft-deleted items.
     *
     * @param string $id The ID of the resource to restore
     * @return Some_Return_Value
     */
    public function restore(string $id)
    {
        $tag = Tag::onlyTrashed()->find($id);
        if(is_null($article)) {
            return $this->sendError('Tag not found.');
        }
        $tag->restore();
        return $this->sendResponse($tag, 'Tag retrieved successfully.');
    }

    /**
     * Force delete a specific resource by ID.
     *
     * @param string $id The ID of the resource to force delete
     * @return Some_Return_Value
     */
    public function forceDelete(string $id)
    {
        $tag = Tag::onlyTrashed()->find($id);
        if(is_null($article)) {
            return $this->sendError('Tag not found.');
        }
        $tag->forceDelete();
        return $this->sendResponse($tag, 'Tags retrieved successfully.');
    }
}
