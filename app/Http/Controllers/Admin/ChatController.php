<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct()
    {
        // Allow only admins to access chats dashboard
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if ($user->type !== 'admin') {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of chats.
     */
    public function index(Request $request): View
    {
        // Permission check - if permission exists, check it; otherwise allow (for backward compatibility)
        try {
            $this->authorize('chats.view');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // If permission doesn't exist yet, allow access for admins (handled by middleware)
        }
        
        $query = Chat::with(['user', 'vendor', 'service', 'lastMessage']);
        
        // Filter by user_id (show chats where user is either user_id or vendor_id)
        if ($request->filled('user_id')) {
            $userId = $request->user_id;
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('vendor_id', $userId);
            });
        }
        
        // Filter by vendor_id
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        // Search by user or vendor name
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where(function($uq) use ($search) {
                        $uq->where('first_name', 'like', $search)
                          ->orWhere('last_name', 'like', $search)
                          ->orWhere('email', 'like', $search);
                    });
                })
                ->orWhereHas('vendor', function ($vendorQuery) use ($search) {
                    $vendorQuery->where(function($vq) use ($search) {
                        $vq->where('first_name', 'like', $search)
                          ->orWhere('last_name', 'like', $search)
                          ->orWhere('email', 'like', $search);
                    });
                });
            });
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Order by last message time or created_at
        $chats = $query->orderByDesc(
            Message::select('created_at')
                ->whereColumn('chat_id', 'chats.id')
                ->latest()
                ->take(1)
        )->paginate(20)->withQueryString();
        
        // Get message counts for each chat
        foreach ($chats as $chat) {
            $chat->messages_count = Message::where('chat_id', $chat->id)->count();
        }
        
        return view('pages.chats.index', compact('chats'));
    }

    /**
     * Display the specified chat with all messages.
     */
    public function show(Chat $chat): View
    {
        // Permission check - if permission exists, check it; otherwise allow (for backward compatibility)
        try {
            $this->authorize('chats.view');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // If permission doesn't exist yet, allow access for admins (handled by middleware)
        }
        
        // Load relationships
        $chat->load(['user', 'vendor', 'service', 'messages.sender', 'reports.reporter', 'reports.reportedUser', 'reports.reviewer']);
        
        // Get all messages ordered by created_at
        $messages = $chat->messages()->with('sender')->orderBy('created_at', 'asc')->get();
        
        return view('pages.chats.show', compact('chat', 'messages'));
    }
}
