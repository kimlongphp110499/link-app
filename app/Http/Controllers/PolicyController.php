<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PolicyController extends Controller
{
    public function show()
    {
        return view('policy.show');
    }

    public function edit()
    {
        $bladePath = resource_path('views/policy/show.blade.php');
        $bladeTermPath = resource_path('views/terms/show.blade.php');
        $policyContent = File::get($bladePath);
        $termContent = File::get($bladeTermPath);
        return view('policy.edit', compact('policyContent', 'termContent'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'content' => 'required',
        ]);

        $bladePath = resource_path('views/policy/show.blade.php');
        $content = $request->content;

        // Lưu nội dung trực tiếp vào file Blade
        File::put($bladePath, $content);

        return redirect()->route('policy.show')->with('success', 'Policy updated successfully.');
    }
}
    // sudo chgrp -R webgroup /var/www/html/link-app
    // sudo find /var/www/html/link-app -type d -exec chmod 775 {} \;
    // sudo find /var/www/html/link-app -type f -exec chmod 664 {} \;
    // public function handle()