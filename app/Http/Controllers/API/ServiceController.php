<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Service;
use Validator;

class ServiceController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $services = Service::query();
        if ($search = $request->input('search')) {
            $services->where('name', 'like', '%' . $search . '%')
            ->orWhere('position', 'like', '%' . $search . '%')
            ->orWhere('message', 'like', '%' . $search . '%');
        }

        if ($sort = $request->input('sort') ?? 'id') {
            $services->orderBy($sort);
        }

        if ($order = $request->input('order') ?? 'asc') {
            $services->orderBy('id', $order);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page', 1);

        $result = $services->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse($result, 'Services retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:255',
            'cover_image' => 'required',
            'description' => 'required|string',
            'icon' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        if(isset($input['cover_image'])) {
            $upload = $this->uploadImageWithThumbnail($input['cover_image'], $input['service_name'], 'services', 150, 93);
            $input['cover_image'] = $upload['image'];
            $input['thumbnail'] = $upload['thumbnail'];
        }
        $services = Service::create($input);
        return $this->sendResponse($services, 'Service created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $service = Service::find($id);
        if(is_null($service)) {
            return $this->sendError('Service not found.');
        }

        return $this->sendResponse($service, 'Service retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $service = Service::find($id);
        if(is_null($service)) {
            return $this->sendError('Service not found.');
        }
        $input = $request->all();
        if(isset($input['cover_image'])) {
            if (!empty($service->cover_image)) {
                $this->removeFiles($service->cover_image);
            }
            if(!empty($service->thumbnail)) {
                $this->removeFiles($service->thumbnail);
            }
            $upload = $this->uploadImageWithThumbnail($input['cover_image'], $input['service_name'], 'services/', 150, 93);
            $input['cover_image'] = $upload['image'];
            $input['thumbnail'] = $upload['thumbnail'];
        }
        $service->update($input);
        return $this->sendResponse($service, 'Service updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service = Service::find($id);
        if(is_null($service)) {
            return $this->sendError('Service not found.');
        }
        $service->delete();
        return $this->sendResponse($service, 'Service deleted successfully.');
    }

    /**
     * Retrieves all trashed services from the database and sends a JSON response
     * containing the services and a success message.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the trashed services and a success message.
     */
    public function trash()
    {
        $services = Service::onlyTrashed()->get();
        return $this->sendResponse($services, 'Services retrieved successfully.');
    }

    /**
     * Restores all trashed services.
     *
     * @throws \Exception If there is an issue restoring the services.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored services and a success message.
     */
    public function restoreAll()
    {
        $services = Service::onlyTrashed()->restore();
        return $this->sendResponse($services, 'Services restored successfully.');
    }

    /**
     * Restores a service with the given ID.
     *
     * @param string $id The ID of the service to restore.
     * @throws \Exception If the service is not found.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored service and a success message.
     */
    public function restore(string $id)
    {
        $services = Service::onlyTrashed()->find($id);
        if(is_null($services)) {
            return $this->sendError('Service not found.');
        }
        $services->restore();
        return $this->sendResponse($services, 'Service restored successfully.');
    }

    /**
     * Permanently deletes a service with the given ID.
     *
     * @param string $id The ID of the service to delete.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the deleted service and a success message.
     */
    public function forceDelete(string $id)
    {
        $service = Service::onlyTrashed()->find($id);
        if(is_null($service)) {
            return $this->sendError('Service not found.');
        }
        if ($service->cover_image) {
            $this->removeFiles($service->cover_image);
        }
        if($service->thumbnail) {
            $this->removeFiles($service->thumbnail);
        }
        $service->forceDelete();
        return $this->sendResponse($service, 'Service deleted successfully.');
    }
}
