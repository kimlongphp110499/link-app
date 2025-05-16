<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHonorRequest;
use App\Models\Honor;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class HonorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $honors = Honor::select('id', 'url_name', 'url', 'date')->paginate(5);
            return view('admin.honors.index', compact('honors'));
        } catch (\Exception $e) {
            Log::error('Failed to retrieve honors list: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
            ]);
            return view('admin.honors.index', [
                'honors' => null,
                'error' => 'Unable to load honors list. Please try again later.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        session()->put('return_url', url()->previous());
        return view('admin.honors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHonorRequest $request)
    {
        try {
            Honor::create($request->validated());
            Redis::publish('honors', json_encode([
                'event' => 'honor.updated',
                'data' => [
                    'action' => 'create',
                    'message' => 'Honors data has changed. Please refresh by calling API.'
                ]
            ]));
            return redirect()->to(session('return_url', route('admin.honors.index')))->with('success', 'Honor created successfully!');
        } catch (QueryException $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create honor. Please try again.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        session()->put('return_url', url()->previous());
        try {
            $honor = Honor::findOrFail($id);
            return view('admin.honors.edit', compact('honor'));
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            return redirect()->route('admin.honors.index')
                ->with('error', 'An unexpected error has occurred.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreHonorRequest $request, $id)
    {
        try {
            $honor = Honor::findOrFail($id);
            $honor->update($request->all());
            $message = [
                'event' => 'honor.updated',
                'data' => [
                    'action' => 'update',
                    'message' => 'Honors data has changed. Please refresh by calling API.'
                ]
            ];
            Redis::publish('honors', json_encode($message));
            Log::info('Published to Redis:', $message);

            return redirect()->to(session('return_url', route('admin.honors.index')))
                ->with('success', 'Honor updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.honors.index')
                ->with('error', 'The honor record you are trying to update was not found.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        session()->put('return_url', url()->previous());
        try {
            DB::beginTransaction();
            $honor = Honor::findOrFail($id);
            $honor->delete();
            Redis::publish('honors', json_encode([
                'event' => 'honor.updated',
                'data' => [
                    'action' => 'delete',
                    'message' => 'Honors data has changed. Please refresh by calling API.'
                ]
            ]));
            DB::commit();

            return redirect()->to(session('return_url', route('admin.honors.index')))
                ->with('success', 'Honor deleted successfully.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->route('admin.honors.index')
                ->with('error', 'An unexpected error occurred while deleting the honor.');
        }
    }
}
