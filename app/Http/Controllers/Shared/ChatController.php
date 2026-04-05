<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\ChatBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(private ChatBotService $chatBot) {}

    public function index()
    {
        return view('shared.chat.index');
    }

    public function session(Request $request): JsonResponse
    {
        $session = ChatSession::firstOrCreate(
            ['user_id' => $request->user()->id, 'is_active' => true],
            ['session_token' => Str::random(64), 'last_activity_at' => now()]
        );

        $session->update(['last_activity_at' => now()]);

        return response()->json(['session_id' => $session->id, 'token' => $session->session_token]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message'    => ['required', 'string', 'max:1000'],
            'session_id' => ['required', 'exists:chat_sessions,id'],
        ]);

        $session = ChatSession::where('id', $data['session_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        ChatMessage::create([
            'session_id'   => $session->id,
            'sender'       => 'user',
            'message'      => $data['message'],
            'message_type' => 'text',
        ]);

        $result = $this->chatBot->processMessage($data['message'], $request->user(), $session);

        $botMessage = ChatMessage::create([
            'session_id'   => $session->id,
            'sender'       => 'bot',
            'message'      => $result['message'],
            'message_type' => 'text',
            'intent'       => $result['intent'],
            'metadata'     => $result['metadata'],
        ]);

        $session->update(['last_activity_at' => now()]);

        return response()->json([
            'message'       => $result['message'],
            'quick_replies' => $result['quick_replies'],
            'message_id'    => $botMessage->id,
        ]);
    }

    public function getHistory(Request $request): JsonResponse
    {
        $session = ChatSession::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if (!$session) {
            return response()->json(['messages' => []]);
        }

        $messages = $session->messages()->orderBy('created_at')->get();
        return response()->json(['messages' => $messages]);
    }
}
