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
        $links = Link::paginate(20);
        $clans = Clan::doesntHave('link')->get(); 
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
        $clans = Clan::whereDoesntHave('link', function ($query) use ($link) {
            $query->where('id', '!=', $link->id);
        })->get();

        return view('admin.links.edit', compact('link', 'clans'));
    }

    // Cập nhật thông tin link
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'clan_id' => 'nullable|exists:clans,id',
        ]);

        $link = Link::findOrFail($id);
        $link->update([
            'title' => $request->title,
            'url' => $request->url,
            'clan_id' => $request->clan_id,
        ]);

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
            'clan_id' => 'required|exists:clans,id',
        ]);

        $link = Link::findOrFail($linkId);
        $clan = Clan::findOrFail($request->clan_id);

        // Kiểm tra nếu link đã có clan
        if ($link->clan) {
            return redirect()->route('admin.links.index')->with('error', 'This link already has a clan assigned.');
        }

        // Kiểm tra nếu clan đã được gắn cho link khác
        if ($clan->link) {
            return redirect()->route('admin.links.index')->with('error', 'This clan is already assigned to another link.');
        }

        // Gắn clan cho link
        $link->clan()->associate($clan);
        $link->save();

        return redirect()->route('admin.links.index')->with('success', 'Clan assigned to link successfully!');
    }
}


