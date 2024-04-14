<?php

namespace LlmLaraHub\TagFunction\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TagFunctionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tagfunction::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tagfunction::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('tagfunction::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('tagfunction::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
