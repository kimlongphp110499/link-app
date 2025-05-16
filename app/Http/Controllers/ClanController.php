<?php

namespace App\Http\Controllers;

use App\Models\Clan;
use Illuminate\Http\Request;

class ClanController extends Controller
{
    // Hiển thị danh sách clans
    public function index()
    {
        $clans = Clan::paginate(20);
        return view('admin.clans.index', compact('clans'));
    }

    // Hiển thị form tạo mới clan
    public function create()
    {
        session()->put('return_url', url()->previous());
        return view('admin.clans.create');
    }

    // Lưu thông tin clan mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191|unique:clans',
        ]);

        $clan = Clan::create([
            'name' => $request->name,
        ]);
        return redirect()->to(session('return_url', route('admin.clans.index')))
        ->with('success', 'Clan created successfully.');
    }

    // Hiển thị form chỉnh sửa clan
    public function edit($id)
    {
        session()->put('return_url', url()->previous());
        $clan = Clan::findOrFail($id);
        return view('admin.clans.edit', compact('clan'));
    }

    // Cập nhật thông tin clan
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:191|unique:clans,name,' . $id,
            'points' => 'required|integer|min:0',
        ]);

        $clan = Clan::findOrFail($id);
        $clan->update([
            'name' => $request->name,
            'points' => $request->points,
        ]);

        return redirect()->to(session('return_url', route('admin.clans.index')))->with('success', 'Clan updated successfully!');
    }

    // Xóa clan
    public function destroy($id)
    {
        session()->put('return_url', url()->previous());
        $clan = Clan::findOrFail($id);
        $clan->delete();

        return redirect()->to(session('return_url', route('admin.clans.index')))
        ->with('success', 'Clan deleted successfully.');
    }
}
