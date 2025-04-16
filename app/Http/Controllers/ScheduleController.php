<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        $videos = Link::all();
        $schedules = Schedule::with('video')->orderBy('start_time')->get();
        return view('schedules.index', compact('videos', 'schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:videos,id',
            'start_time' => 'required|date'
        ]);

        Schedule::create($request->all());
        return redirect()->back()->with('success', 'Schedule added');
    }
}