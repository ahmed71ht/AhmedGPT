<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;

class ChatController extends Controller
{  
    public function send(Request $request)
    {
        $msg = $request->message;
        $userId = auth()->id();

        // حفظ رسالة المستخدم
        Message::create([
            'message' => $msg,
            'sender' => 'user',
            'user_id' => $userId
        ]);

        $lower = strtolower($msg);

        if (str_contains($lower, 'مرحبا')) {
            $reply = 'أهلًا 👋';
        } elseif (str_contains($lower, 'كيفك')) {
            $reply = 'تمام 😄';
        } else {
            $random = ['😂', 'حلو!', 'تمام 👍', 'أنا بوت 🤖'];
            $reply = $random[array_rand($random)];
        }

        // حفظ رد البوت
        Message::create([
            'message' => $reply,
            'sender' => 'bot',
            'user_id' => $userId
        ]);

        return response()->json([
            'reply' => $reply
        ]);
    }

    public function messages()
    {
        return response()->json(
            Message::where('user_id', auth()->id())
                ->latest()
                ->take(50)
                ->get()
                ->reverse()
                ->values()
        );
    }
}