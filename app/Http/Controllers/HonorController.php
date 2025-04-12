<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHonorRequest;
use App\Models\Honor;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HonorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $honors = Honor::select('id', 'url_name', 'url', 'date')->paginate(20);
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
        return view('admin.honors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHonorRequest $request)
    {
        try {
            Honor::create($request->validated());
            return redirect()->route('admin.honors.index')->with('success', 'Honor created successfully!');
        } catch (QueryException $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create honor. Please try again.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $honor = Honor::findOrFail($id);
            return view('admin.honors.edit', compact('honor'));
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            return redirect()->route('admin.honors.index')
                ->with('error', 'The record you are trying to edit was not found.');
        } catch (\Exception $e) {
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

            return redirect()->route('admin.honors.index')
                ->with('success', 'Honor updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Record not found
            return redirect()->route('admin.honors.index')
                ->with('error', 'The honor record you are trying to update was not found.');

        } catch (\Exception $e) {
            // General exception
            return redirect()->route('admin.honors.index')
                ->with('error', 'An unexpected error occurred while updating the honor.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $honor = Honor::findOrFail($id);
            $honor->delete();
            DB::commit();

            return redirect()->route('admin.honors.index')
                ->with('success', 'Honor deleted successfully.');
        } catch (ModelNotFoundException $e) {
            // Log lỗi khi không tìm thấy bản ghi
            Log::error('Honor not found for deletion: ID ' . $id);
            return redirect()->route('admin.honors.index')
                ->with('error', 'Honor not found.');
        } catch (QueryException $e) {
            // Rollback nếu có lỗi database
            DB::rollBack();
            Log::error('Database error while deleting honor ID ' . $id . ': ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
            ]);
            return redirect()->route('admin.honors.index')
                ->with('error', 'Failed to delete honor due to a database error.');
        } catch (\Exception $e) {
            // Rollback cho các lỗi khác
            DB::rollBack();
            Log::error('Unexpected error while deleting honor ID ' . $id . ': ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
            ]);
            return redirect()->route('admin.honors.index')
                ->with('error', 'An unexpected error occurred while deleting the honor.');
        }
    }
}
