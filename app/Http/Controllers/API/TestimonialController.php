<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Testimonial;

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
