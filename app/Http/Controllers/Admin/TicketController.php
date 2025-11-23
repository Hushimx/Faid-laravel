<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function __construct()
    {
        // Allow only admins to access tickets dashboard
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if ($user->type !== 'admin') {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of tickets.
     */
    public function index(Request $request): View
    {
        $this->authorize('tickets.view');
        
        $user = Auth::user();
        
        $query = Ticket::with(['user', 'assignedAdmin', 'latestMessage']);
        
        // Admins see all tickets
        // Admins can filter by status, priority, user type, etc.
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
                  ->orWhere('description', 'like', $search)
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where(function($q) use ($search) {
                          $q->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                      });
                  });
            });
        }
        
        // Filter by user type (vendor or user)
        if ($request->filled('user_type')) {
            $query->whereHas('user', function ($userQuery) use ($request) {
                $userQuery->where('type', $request->user_type);
            });
        }
        
        $tickets = $query->latest()->paginate(15)->withQueryString();
        
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
            'vendors' => Ticket::whereHas('user', function ($q) {
                $q->where('type', 'vendor');
            })->count(),
            'users' => Ticket::whereHas('user', function ($q) {
                $q->where('type', 'user');
            })->count(),
        ];
        
        return view('pages.tickets.index', compact('tickets', 'stats'));
    }


    /**
     * Display the specified ticket with messages.
     */
    public function show(Ticket $ticket): View
    {
        $this->authorize('tickets.view');
        
        $user = Auth::user();
        
        // Load relationships
        $ticket->load(['user', 'assignedAdmin', 'messages.user']);
        
        // Mark unread messages as read for admin
        $ticket->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return view('pages.tickets.show', compact('ticket'));
    }

    /**
     * Update the specified ticket (open/close).
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('tickets.edit');
        
        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);
        
        if ($validated['status'] === 'closed') {
            $ticket->close();
        } else {
            $ticket->open();
        }
        
        // Assign ticket to admin if provided
        if (isset($validated['assigned_to'])) {
            $ticket->update(['assigned_to' => $validated['assigned_to']]);
        }
        
        return redirect()->back()
            ->with('success', __('dashboard.Ticket updated successfully'));
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('tickets.delete');
        
        $ticket->delete();
        
        return redirect()->route('tickets.index')
            ->with('success', __('dashboard.Ticket deleted successfully'));
    }
}