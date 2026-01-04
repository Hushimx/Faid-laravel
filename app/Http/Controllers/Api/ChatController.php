<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{

    public function chats(Request $request)
    {
        $user = Auth::user();


        if ($user->type === 'vendor') {

            $chats = Chat::where('vendor_id', $user->id)
                ->with(['user', 'service', 'lastMessage'])
                ->orderByDesc(
                    Message::select('created_at')
                        ->whereColumn('chat_id', 'chats.id')
                        ->latest()
                        ->take(1)
                )
                ->get();
        } elseif ($user->type === 'user') {

            $chats = Chat::where('user_id', $user->id)
                ->with(['vendor', 'service', 'lastMessage'])
                ->orderByDesc(
                    Message::select('created_at')
                        ->whereColumn('chat_id', 'chats.id')
                        ->latest()
                        ->take(1)
                )
                ->get();
        } else {
            return ApiResponse::error('Invalid user type.');
        }

        return ApiResponse::success($chats, 'Chats retrieved successfully.');
    }
    public function startChat(Request $request)
    {
        $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'vendor_id' => 'required|integer|exists:users,id',
        ]);

        $user_id = Auth::id();

        $chat = Chat::firstOrCreate(
            [
                'service_id' => $request->service_id,
                'user_id' => $user_id,
                'vendor_id' => $request->vendor_id,
            ]
        );

        return ApiResponse::success($chat, 'Chat started successfully.');
    }

    public function send(Request $request, Chat $chat)
    {
        $request->validate([
            'message' => 'required_without_all:file,latitude|string',
            'file' => 'required_without_all:message,latitude|file|max:10240', // max 10MB
            'latitude' => 'required_with:longitude|numeric',
            'longitude' => 'required_with:latitude|numeric',
        ]);

        $data = [
            'chat_id' => $chat->id,
            'sender_id' => Auth::id(),
        ];

        if ($request->hasFile('file')) {

            $path = uploadFile($request->file('file'), 'chat_files');
            $type = $request->file('file')->getClientOriginalExtension();

            $data['message_type'] = 'file';
            $data['file_path'] = $path;
            $data['file_type'] = $type;
            // Include message if provided with file
            if ($request->has('message')) {
                $data['message'] = $request->message;
            }
        } elseif ($request->has('latitude') && $request->has('longitude')) {
            // Location message - no text required, just like WhatsApp
            $data['message_type'] = 'location';
            $data['message'] = null; // No text message for location
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
        } elseif ($request->has('message')) {
            $data['message_type'] = 'text';
            $data['message'] = $request->message;
        }

        $message = Message::create($data);

        return ApiResponse::success($message, 'Message sent successfully.');
    }

    public function messages(Chat $chat)
    {
        $messages = $chat->messages()->orderBy('created_at', 'asc')->get();

        return ApiResponse::success($messages, 'Messages retrieved successfully.');
    }
}
