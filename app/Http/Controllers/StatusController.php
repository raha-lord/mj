<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Status;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
            $status = Status::where('slug', 'LIKE', "%$keyword%")
                ->orWhere('name', 'LIKE', "%$keyword%")
                ->orWhere('description', 'LIKE', "%$keyword%")
                ->latest()->paginate($perPage);
        } else {
            $status = Status::latest()->paginate($perPage);
        }

        return view('statuses.index', compact('status'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        return view('statuses.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Redirector
     */
    public function store(Request $request)
    {

        $requestData = $request->all();

        Status::create($requestData);

        return redirect('statuses')->with('flash_message', 'Status added!');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return View
     */
    public function show($id)
    {
        $statuscode = Status::findOrFail($id);

        return view('statuses.show', compact('statuscode'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return View
     */
    public function edit($id)
    {
        $statuscode = Status::findOrFail($id);

        return view('statuses.edit', compact('statuscode'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     *
     * @return RedirectResponse|Redirector
     */
    public function update(Request $request, $id)
    {

        $requestData = $request->all();

        $statuscode = Status::findOrFail($id);
        $statuscode->update($requestData);

        return redirect('statuses')->with('flash_message', 'Status updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse|Redirector
     */
    public function destroy($id)
    {
        Status::destroy($id);

        return redirect('statuses')->with('flash_message', 'Status deleted!');
    }
}
