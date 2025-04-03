<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use App\Models\Clan;

class LinkController extends Controller
{
    // Hiển thị danh sách links
    public function index()
    {
        $links = Link::with('clans')->paginate(20);
        $clans = Clan::all();
        return view('admin.links.index', compact('links', 'clans'));
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
        $clans = Clan::all();
        $selectedClans = $link->clans->pluck('id')->toArray();
        return view('admin.links.edit', compact('link', 'clans', 'selectedClans'));
    }

    // Cập nhật thông tin link
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'clan_ids' => 'nullable|array',
        ]);

        $link = Link::findOrFail($id);
        $link->update([
            'title' => $request->title,
            'url' => $request->url,
        ]);
        $link->clans()->sync($request->clan_ids);


        return redirect()->route('admin.links.index')->with('success', 'Link updated successfully!');
    }

    // Xóa link
    public function destroy($id)
    {
        Link::findOrFail($id)->delete();
        return redirect()->route('admin.links.index')->with('success', 'Link deleted successfully!');
    }

    // Gắn clan cho link
    public function assignClan(Request $request, $linkId)
    {
        $request->validate([
            'clan_ids' => 'required|array',
        ]);
    
        $link = Link::findOrFail($linkId);
        $link->clans()->sync($request->clan_ids);

        return redirect()->route('admin.links.index')->with('success', 'Clan assigned to link successfully!');
    }
}


