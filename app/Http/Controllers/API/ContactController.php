<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Contact;
use Validator;

class ContactController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $contacts = Contact::query();
        if ($search = $request->input('search')) {
            $contacts->where('name', 'like', '%' . $search . '%')
            ->orWhere('message', 'like', '%' . $search . '%');
        }

        if ($sort = $request->input('sort') ?? 'id') {
            $contacts->orderBy($sort);
        }

        if ($order = $request->input('order') ?? 'asc') {
            $contacts->orderBy('id', $order);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page', 1);

        $result = $contacts->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse($result, 'Contacts retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:255',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $contact = Contact::create($input);
        return $this->sendResponse($contact, 'Contact created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contact = Contact::find($id);
        if($contact === null) {
            return $this->sendError('Contact not found.');
        }
        return $this->sendResponse($contact, 'Contact retrieved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $contact = Contact::find($id);
        if($contact === null) {
            return $this->sendError('Contact not found.');
        }
        $contact->delete();
        return $this->sendResponse($contact, 'Contact retrieved successfully.');
    }

    /**
     * Retrieves all contacts that are soft deleted.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the retrieved contacts.
     */
    public function trash()
    {
        $contacts = Contact::onlyTrashed()->get();
        return $this->sendResponse($contacts, 'Contacts retrieved successfully.');
    }

    /**
     * Restores all contacts from the trash.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored contacts.
     */
    public function restoreAll()
    {
        $contacts = Contact::onlyTrashed()->restore();
        return $this->sendResponse($contacts, 'Contacts retrieved successfully.');
    }

    /**
     * Restores a contact from the trash based on the provided ID.
     *
     * @param string $id The ID of the contact to restore.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored contact or an error message if the contact is not found.
     */
    public function restore(string $id)
    {
        $contacts = Contact::onlyTrashed()->where('id', $id);
        if($contacts === null) {
            return $this->sendError('Contact not found.');
        }
        $contacts->restore();
        return $this->sendResponse($contacts, 'Contacts retrieved successfully.');
    }

    /**
     * Permanently deletes a contact from the trash.
     *
     * @param string $id The ID of the contact to delete permanently.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the deleted contact.
     */
     public function forceDelete(string $id)
    {
        $contacts = Contact::onlyTrashed()->where('id', $id);
        if($contacts === null) {
            return $this->sendError('Contact not found.');
        }
        $contacts->forceDelete();
        return $this->sendResponse($contacts, 'Contacts retrieved successfully.');
    }
}
