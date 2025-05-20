<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TermsController extends Controller
{
    public function show()
    {
        return view('terms.show');
    }

    public function update(Request $request)
    {
        $request->validate([
            'content' => 'required',
        ]);

        $bladePath = resource_path('views/terms/show.blade.php');
        $content = $request->content;

        File::put($bladePath, $content);

        return redirect()->route('term.show')->with('success', 'Terms updated successfully.');
        //return back()->with('success', 'terms updated successfully.');
    }
}
    // sudo chgrp -R webgroup /var/www/html/link-app
    // sudo find /var/www/html/link-app -type d -exec chmod 775 {} \;
    // sudo find /var/www/html/link-app -type f -exec chmod 664 {} \;
    // public function handle()