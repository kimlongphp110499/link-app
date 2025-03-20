<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Jobs\SaveMessage;

class MessageController extends Controller
{
    public function saveMessage(Request $request)
    {
        // Gửi job lưu tin nhắn vào queue
        dispatch(new SaveMessage($request->message, $request->user_id));

        return response()->json(['message' => 'Message saved to queue.']);
    }
}