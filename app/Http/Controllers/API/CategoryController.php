<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Category;
use Validator;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::query();
        if ($search = $request->input('search')) {
            $categories->where('name', 'like', '%' . $search . '%')
            ->orWhere('description', 'like', '%' . $search . '%');
        }

        if ($sort = $request->input('sort') ?? 'id') {
            $categories->orderBy($sort);
        }

        if ($order = $request->input('order') ?? 'asc') {
            $categories->orderBy('id', $order);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page', 1);

        $result = $categories->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse($result, 'Categories retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['slug'] = \Str::slug($request->input('name'));
        $category = Category::create($input);
        return $this->sendResponse($category, 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::find($id);
        if(is_null($category)){
            return $this->sendError('Category not found.');
        }
        return $this->sendResponse($category, 'Category retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $category = Category::find($id);
        if(is_null($category)){
            return $this->sendError('Category not found.');
        }
        $input = $request->all();
        $inp['slug'] = \Str::slug($request->input('name'));
        $category->update($input);
        return $this->sendResponse($category, 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        if(is_null($category)){
            return $this->sendError('Category not found.');
        }
        $category->delete();
        return $this->sendResponse($category, 'Category deleted successfully.');
    }

    /**
     * Retrieves categories based on the provided search term.
     *
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the retrieved categories.
     */
    public function getCategories(Request $request)
    {
        $categories = Category::query();
        if($request->input('name')) {
            $categories->where('name', 'like', '%' . $request->input('name') . '%');
        }
        $categories = $categories->get();
        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    /**
     * Retrieves the trashed categories from the database.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the retrieved categories.
     */
    public function trash()
    {
        $categories = Category::onlyTrashed()->get();
        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    /**
     * Restores all trashed categories.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored categories.
     */
    public function restoreAll()
    {
        $categories = Category::onlyTrashed()->restore();
        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    /**
     * Restores a specific trashed category.
     *
     * @param int $id The ID of the category to restore.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored category.
     */
    public function restore($id)
    {
        $categories = Category::onlyTrashed()->find($id);
        if(is_null($categories)) {
            return $this->sendError('Category not found.');
        }
        $categories->restore();
        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    /**
     * Permanently deletes a specific category.
     *
     * @param int $id The ID of the category to delete.
     * @throws \Exception If an error occurs during the deletion process.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the deleted category and a success message.
     */
    public function forceDelete($id)
    {
        $categories = Category::onlyTrashed()->where('id', $id);
        if(is_null($categories)) {
            return $this->sendError('Category not found.');
        }
        $categories->forceDelete();
        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }
}
