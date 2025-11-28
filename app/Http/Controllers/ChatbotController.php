<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatbotResponse;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot.index'); // Blade with chat UI
    }

    public function getResponse(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = strtolower($request->message);

        // Simple match: find first query that contains user message
        $response = ChatbotResponse::all()->first(function ($item) use ($message) {
            return str_contains(strtolower($item->question), $message);
        });

        if ($response) {
            return response()->json(['status' => 'success', 'reply' => $response->response]);
        } else {
            return response()->json(['status' => 'success', 'reply' => "Sorry, I didn't understand that."]);
        }
    }
}
