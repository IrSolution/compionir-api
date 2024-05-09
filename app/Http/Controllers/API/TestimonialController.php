<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Testimonial;
use Validator;

class TestimonialController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $testimonilas = Testimonial::query();
        if ($search = $request->input('search')) {
            $testimonilas->where('customer_name', 'like', '%' . $search . '%')
            ->orWhere('message', 'like', '%' . $search . '%');
        }

        if ($sort = $request->input('sort') ?? 'id') {
            $testimonilas->orderBy($sort);
        }

        if ($order = $request->input('order') ?? 'asc') {
            $testimonilas->orderBy('id', $order);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page', 1);

        $result = $testimonilas->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse($result, 'Testimonial retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'customer_name' => 'required|string|max:255',
            'message' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        if(isset($input['avatar'])) {
            $upload = $this->uploadImageWithThumbnail($input['avatar'], $input['customer_name'], 'testimonials/', 150, 93);
            $input['avatar'] = $upload['image'];
            $input['thumbnail'] = $upload['thumbnail'];
        }
        $testimonilas = Testimonial::create($input);
        return $this->sendResponse($testimonilas, 'Testimonial created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $testimonial = Testimonial::find($id);
        if(is_null($testimonial)) {
            return $this->sendError('Testimonial not found.');
        }

        return $this->sendResponse($testimonial, 'Testimonial retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'customer_name' => 'required|string|max:255',
            'message' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $testimonial = Testimonial::find($id);
        if(is_null($testimonial)) {
            return $this->sendError('Testimonial not found.');
        }
        $input = $request->all();
        if(isset($input['avatar'])) {
            if($testimonial->avatar) {
                $this->removeFiles($testimonial->avatar);
                $this->removeFiles($testimonial->thumbnail);
            }
            $upload = $this->uploadImageWithThumbnail($input['avatar'], $input['customer_name'], 'testimonials/', 150, 93);
            $input['avatar'] = $upload['image'];
            $input['thumbnail'] = $upload['thumbnail'];
        }

        $testimonial->update($input);
        return $this->sendResponse($testimonial, 'Testimonial updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $testimonial = Testimonial::find($id);
        if(is_null($testimonial)) {
            return $this->sendError('Testimonial not found.');
        }

        $testimonial->delete();
        return $this->sendResponse($testimonial, 'Testimonial deleted successfully.');
    }

    /**
     * Retrieves all the soft deleted testimonials from the database.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the testimonials and a success message.
     */
    public function trash()
    {
        $testimonials = Testimonial::onlyTrashed()->get();
        return $this->sendResponse($testimonials, 'Testimonials retrieved successfully.');
    }

    /**
     * Restores all soft deleted testimonials from the database.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored testimonials and a success message.
     */
    public function restoreAll()
    {
        $testimonials = Testimonial::onlyTrashed()->restore();
        return $this->sendResponse($testimonials, 'Testimonials retrieved successfully.');
    }

    /**
     * Restores a soft deleted testimonial from the database.
     *
     * @param int $id The ID of the testimonial to restore.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored testimonial and a success message.
     */
    public function restore($id)
    {
        $testimonial = Testimonial::onlyTrashed()->find($id);
        if(is_null($testimonial)) {
            return $this->sendError('Testimonial not found.');
        }

        $testimonial->restore();
        return $this->sendResponse($testimonial, 'Testimonial retrieved successfully.');
    }

    /**
     * Deletes all soft deleted testimonials permanently from the database.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the deleted testimonials and a success message.
     */
    public function forceDeleteAll()
    {
        $testimonials = Testimonial::onlyTrashed()->forceDelete();
        return $this->sendResponse($testimonials, 'Testimonials deleted permanently successfully.');
    }

    /**
     * Deletes a testimonial permanently from the database.
     *
     * @param int $id The ID of the testimonial to be deleted.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the deleted testimonial and a success message.
     */
    public function forceDelete($id)
    {
        $testimonial = Testimonial::onlyTrashed()->find($id);
        if(is_null($testimonial)) {
            return $this->sendError('Testimonial not found.');
        }
        if($testimonial->avatar) {
            $this->removeFiles($testimonial->avatar);
            $this->removeFiles($testimonial->thumbnail);
        }

        $testimonial->forceDelete();
        return $this->sendResponse($testimonial, 'Testimonial deleted permanently successfully.');
    }
}
