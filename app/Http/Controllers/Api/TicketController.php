<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /**
     * Display a listing of the user's tickets.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Only allow users and vendors to access their tickets
        if (!in_array($user->type, ['user', 'vendor'])) {
            return ApiResponse::error('Unauthorized', [], 403);
        }
        
        $query = Ticket::with(['user', 'assignedAdmin', 'latestMessage'])
            ->where('user_id', $user->id);
        
        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }
        
        $perPage = $request->integer('per_page', 15);
        $tickets = $query->latest()->paginate($perPage);
        
        $tickets->getCollection()->transform(function ($ticket) {
            return new TicketResource($ticket);
        });
        
        return ApiResponse::paginated(
            $tickets,
            'Tickets retrieved successfully'
        );
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // Only allow users and vendors to create tickets
        if (!in_array($user->type, ['user', 'vendor'])) {
            return ApiResponse::error('Unauthorized', [], 403);
        }
        
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'in:low,medium,high'],
        ]);
        
        $ticket = Ticket::create([
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
        ]);
        
        $ticket->load(['user', 'assignedAdmin']);
        
        return ApiResponse::success(
            new TicketResource($ticket),
            'Ticket created successfully',
            201
        );
    }

    /**
     * Display the specified ticket.
     */
    public function show(Request $request, Ticket $ticket)
    {
        $user = $request->user();
        
        // Only allow users and vendors to view their own tickets
        if (!in_array($user->type, ['user', 'vendor'])) {
            return ApiResponse::error('Unauthorized', [], 403);
        }
        
        // Check if ticket belongs to user
        if ($ticket->user_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }
        
        // Load relationships
        $ticket->load(['user', 'assignedAdmin', 'messages.user']);
        
        // Mark unread messages as read
        $ticket->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return ApiResponse::success(
            new TicketResource($ticket),
            'Ticket retrieved successfully'
        );
    }

    /**
     * Update the specified ticket (open/close only).
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = $request->user();
        
        // Only allow users and vendors to update their own tickets
        if (!in_array($user->type, ['user', 'vendor'])) {
            return ApiResponse::error('Unauthorized', [], 403);
        }
        
        // Check if ticket belongs to user
        if ($ticket->user_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }
        
        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);
        
        if ($validated['status'] === 'closed') {
            $ticket->close();
        } else {
            $ticket->open();
        }
        
        $ticket->load(['user', 'assignedAdmin']);
        
        return ApiResponse::success(
            new TicketResource($ticket),
            'Ticket updated successfully'
        );
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy(Request $request, Ticket $ticket)
    {
        $user = $request->user();
        
        // Only allow users and vendors to delete their own tickets
        if (!in_array($user->type, ['user', 'vendor'])) {
            return ApiResponse::error('Unauthorized', [], 403);
        }
        
        // Check if ticket belongs to user
        if ($ticket->user_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }
        
        $ticket->delete();
        
        return ApiResponse::success(
            null,
            'Ticket deleted successfully'
        );
    }
}