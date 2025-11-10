<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketMessageController extends Controller
{

    /**
     * Store a new message for a ticket.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        
        // Check if ticket is closed
        if ($ticket->isClosed()) {
            return response()->json([
                'success' => false,
                'message' => __('dashboard.Cannot send message to closed ticket'),
            ], 400);
        }
        
        $validated = $request->validate([
            'message' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'], // 10MB max
        ]);
        
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('ticket-attachments', 'public');
        }
        
        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
            'is_read' => false,
        ]);
        
        // If admin sends a message, assign the ticket to them
        if (!$ticket->assigned_to) {
            $ticket->update(['assigned_to' => $user->id]);
        }
        
        // Load user relationship for response
        $message->load('user');
        
        return back();
    }

    /**
     * Get messages for a ticket (AJAX).
     */
    public function index(Request $request, Ticket $ticket): JsonResponse
    {
        $user = Auth::user();
        
        $messages = $ticket->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Mark unread messages as read
        $ticket->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        $formattedMessages = $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'message' => $message->message,
                'attachment' => $message->attachment ? Storage::url($message->attachment) : null,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->first_name . ' ' . $message->user->last_name,
                    'type' => $message->user->type,
                    'profile_picture' => $message->user->profile_picture ? Storage::url($message->user->profile_picture) : null,
                ],
                'is_read' => $message->is_read,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $message->created_at->diffForHumans(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $formattedMessages,
        ]);
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(Request $request, Ticket $ticket): JsonResponse
    {
        $user = Auth::user();
        
        $ticket->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json([
            'success' => true,
            'message' => __('dashboard.Messages marked as read'),
        ]);
    }
}