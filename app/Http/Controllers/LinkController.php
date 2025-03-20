<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    // Hiển thị danh sách links
    public function index()
    {
        $links = Link::all();
        return view('admin.links.index', compact('links'));
    }

    // Hiển thị form tạo link
    public function create()
    {
        return view('admin.links.create');
    }

    // Lưu thông tin link mới
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
        ]);

        Link::create([
            'title' => $request->title,
            'url' => $request->url,
        ]);

        return redirect()->route('admin.links.index')->with('success', 'Link created successfully!');
    }

    // Hiển thị form chỉnh sửa link
    public function edit($id)
    {
        $link = Link::findOrFail($id);
        return view('admin.links.edit', compact('link'));
    }

    // Cập nhật thông tin link
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
        ]);

        $link = Link::findOrFail($id);
        $link->update([
            'title' => $request->title,
            'url' => $request->url,
        ]);

        return redirect()->route('admin.links.index')->with('success', 'Link updated successfully!');
    }

    // Xóa link
    public function destroy($id)
    {
        Link::findOrFail($id)->delete();
        return redirect()->route('admin.links.index')->with('success', 'Link deleted successfully!');
    }
}


