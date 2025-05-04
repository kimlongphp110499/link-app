<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CKFinderController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = public_path('uploads/images');
            $file->move($path, $filename);

            return response()->json([
                'uploaded' => 1,
                'fileName' => $filename,
                'url' => asset('uploads/images/' . $filename)
            ]);
        }

        return response()->json([
            'uploaded' => 0,
            'error' => ['message' => 'No file uploaded']
        ], 400);
    }
}