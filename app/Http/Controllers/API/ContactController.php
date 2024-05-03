<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Contact;

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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
