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
        try {
            \Log::info('TicketMessage store called', [
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()?->id,
                'has_message' => $request->has('message'),
                'has_attachment' => $request->hasFile('attachment'),
            ]);

            $user = $request->user();

            if (!$user) {
                \Log::error('TicketMessage store: No authenticated user');
                return ApiResponse::error('Unauthenticated', [], 401);
            }

            // Only allow users and vendors to send messages
            if (!in_array($user->type, ['user', 'vendor'])) {
                \Log::error('TicketMessage store: Unauthorized user type', [
                    'user_type' => $user->type,
                    'user_id' => $user->id,
                ]);
                return ApiResponse::error('Unauthorized', [], 403);
            }

            // Check if ticket belongs to user
            if ($ticket->user_id !== $user->id) {
                \Log::error('TicketMessage store: Ticket does not belong to user', [
                    'ticket_user_id' => $ticket->user_id,
                    'request_user_id' => $user->id,
                ]);
                return ApiResponse::error('Unauthorized', [], 403);
            }

            // Check if ticket is closed
            if ($ticket->isClosed()) {
                \Log::warning('TicketMessage store: Attempted to send message to closed ticket', [
                    'ticket_id' => $ticket->id,
                    'ticket_status' => $ticket->status,
                ]);
                return ApiResponse::error('Cannot send message to closed ticket', [], 400);
            }

            // Allow empty message if there's an attachment
            $rules = [
                'message' => ['nullable', 'string', 'max:5000'],
                'attachment' => ['nullable', 'file', 'max:10240'], // 10MB max
            ];
            
            // Require either message or attachment
            $hasMessage = $request->has('message') && !empty(trim($request->input('message', '')));
            $hasAttachment = $request->hasFile('attachment');
            
            if (!$hasMessage && !$hasAttachment) {
                \Log::error('TicketMessage store: Neither message nor attachment provided');
                return ApiResponse::error('Either message or attachment is required', [], 422);
            }
            
            $validated = $request->validate($rules);

            \Log::info('TicketMessage validation passed', [
                'message_length' => strlen($validated['message'] ?? ''),
                'has_attachment' => $request->hasFile('attachment'),
            ]);

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                try {
                    $attachmentPath = $request->file('attachment')->store('ticket-attachments', 'public');
                    \Log::info('TicketMessage attachment stored', [
                        'attachment_path' => $attachmentPath,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('TicketMessage attachment storage failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return ApiResponse::error('Failed to store attachment', ['attachment' => $e->getMessage()], 500);
                }
            }

            try {
                $message = TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'message' => $validated['message'] ?? '',
                    'attachment' => $attachmentPath,
                    'is_read' => false,
                ]);

                \Log::info('TicketMessage created successfully', [
                    'message_id' => $message->id,
                    'ticket_id' => $ticket->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('TicketMessage creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                ]);
                return ApiResponse::error('Failed to create message', ['error' => $e->getMessage()], 500);
            }

            try {
                broadcast(new TicketMessageSent($message));
            } catch (\Exception $e) {
                // Log the error but don't fail the request
                \Log::error('Broadcasting TicketMessageSent failed', [
                    'error' => $e->getMessage(),
                    'message_id' => $message->id,
                ]);
            }

            $message->load('user');

            return ApiResponse::success(
                new TicketMessageResource($message),
                'Message sent successfully',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('TicketMessage validation failed', [
                'errors' => $e->errors(),
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()?->id,
            ]);
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('TicketMessage store error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()?->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return ApiResponse::error(
                'An error occurred while sending the message',
                ['error' => $e->getMessage()],
                500
            );
        }
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
