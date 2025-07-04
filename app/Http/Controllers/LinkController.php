<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use App\Models\Clan;
use App\Models\Schedule;
use Illuminate\Support\Facades\Log;
use App\Services\LinkService;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LinksImport;

class LinkController extends Controller
{
    protected $linkService;

    /**
     * Inject LinkService in Controller.
     *
     * @param linkService $linkService
     */
    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    // Hiển thị danh sách links
    public function index()
    {
        $links = Link::with('clans')->orderByDesc('total_votes')->paginate(20);
        $clans = Clan::all();
        $checkSchedule = Schedule::count();

        return view('admin.links.index', compact('links', 'clans', 'checkSchedule'));
    }

    // Hiển thị form tạo link
    public function create()
    {
        session()->put('return_url', url()->previous());
        $clans = Clan::all();

        return view('admin.links.create', compact('clans'));
    }

    // Lưu thông tin link mới
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'video_id' => 'required|string|max:100|unique:links',
            'duration' => 'required|integer|min:1',
            'clan_ids' => 'nullable|array',
        ]);

        $link = Link::create([
            'title' => $request->title,
            'url' => $request->url,
            'video_id' => $request->video_id,
            'duration' => $request->duration,
        ]);

        $link->clans()->sync($request->clan_ids);

        return redirect()->to(session('return_url', route('admin.links.index')))
        ->with('success', 'Link created successfully.');
    }

    // Hiển thị form chỉnh sửa link
    public function edit($id)
    {
        session()->put('return_url', url()->previous());
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
            'video_id' => 'required|string|max:100|unique:links,video_id,' . $id,
            'clan_ids' => 'nullable|array',
            'duration' => 'required|integer|min:1',
            'total_votes' => 'nullable|integer|min:0',
        ]);


        $link = Link::findOrFail($id);
        $link->update([
            'title' => $request->title,
            'url' => $request->url,
            'video_id' => $request->video_id,
            'duration' => $request->duration,
            'total_votes' => $request->total_votes,
        ]);
        $link->clans()->sync($request->clan_ids);

        return redirect()->to(session('return_url', route('admin.links.index')))
        ->with('success', 'Link updated successfully.');
    }

    // Xóa link
    public function destroy($id)
    {
        session()->put('return_url', url()->previous());
        Link::findOrFail($id)->delete();

        return redirect()->to(session('return_url', route('admin.links.index')))
        ->with('success', 'Link updated successfully.');
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

    public function videoStatus(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        $key = strtoupper($request->key);
        $value = $request->value;

        try {
            if($value == 'true') {
                $this->linkService->videoSchedule();
               
            } else {
                Schedule::truncate();
            }

            return response()->json(['message' => 'Environment variable updated successfully']);
        } catch (\Exception $e) {
            Log::error("Error updating .env: " . $e->getMessage());
            return response()->json(['message' => 'Error updating .env file'], 500);
        }
    }
    /**
     * Import data từ file Excel.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        Excel::import(new LinksImport, $request->file('file'));

        return redirect()->route('admin.links.index')->with('success', 'Data imported successfully!');
    }
}