<?php

namespace App\Http\Controllers\Api;

use App\Events\TicketMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketMessageResource;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketMessageController extends Controller
{
    /**
     * Get messages for a ticket.
     */
    public function index(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Only allow users and vendors to view messages
        if (!in_array($user->type, ['user', 'vendor'])) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        // Check if ticket belongs to user
        if ($ticket->user_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        $messages = $ticket->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark unread messages as read
        $ticket->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return ApiResponse::success(
            TicketMessageResource::collection($messages),
            'Messages retrieved successfully'
        );
    }

    /**
     * Store a new message for a ticket.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Only allow users and vendors to send messages
        if (!in_array($user->type, ['user', 'vendor'])) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        // Check if ticket belongs to user
        if ($ticket->user_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        // Check if ticket is closed
        if ($ticket->isClosed()) {
            return ApiResponse::error('Cannot send message to closed ticket', [], 400);
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

        try {
            broadcast(new TicketMessageSent($message));
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            logger()->error('Broadcasting TicketMessageSent failed: ' . $e->getMessage());
        }
        $message->load('user');

        return ApiResponse::success(
            new TicketMessageResource($message),
            'Message sent successfully',
            201
        );
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Only allow users and vendors to mark messages as read
        if (!in_array($user->type, ['user', 'vendor'])) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        // Check if ticket belongs to user
        if ($ticket->user_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        $ticket->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return ApiResponse::success(
            null,
            'Messages marked as read'
        );
    }
}
