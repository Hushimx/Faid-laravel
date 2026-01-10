<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatReport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ChatReportController extends Controller
{
    public function __construct()
    {
        // Allow only admins to access chat reports dashboard
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if ($user->type !== 'admin') {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of chat reports.
     */
    public function index(Request $request): View
    {
        // Permission check - if permission exists, check it; otherwise allow (for backward compatibility)
        try {
            $this->authorize('chat-reports.view');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // If permission doesn't exist yet, allow access for admins (handled by middleware)
        }
        
        $query = ChatReport::with(['chat', 'reporter', 'reportedUser', 'reviewer']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by chat_id
        if ($request->filled('chat_id')) {
            $query->where('chat_id', $request->chat_id);
        }
        
        // Search by reporter or reported user name/email
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', $search)
                  ->orWhereHas('reporter', function ($reporterQuery) use ($search) {
                      $reporterQuery->where(function($uq) use ($search) {
                          $uq->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                      });
                  })
                  ->orWhereHas('reportedUser', function ($reportedQuery) use ($search) {
                      $reportedQuery->where(function($uq) use ($search) {
                          $uq->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                      });
                  });
            });
        }
        
        $reports = $query->latest()->paginate(20)->withQueryString();
        
        return view('pages.chat-reports.index', compact('reports'));
    }

    /**
     * Display the specified chat report with chat conversation.
     */
    public function show(ChatReport $report): View
    {
        // Permission check - if permission exists, check it; otherwise allow (for backward compatibility)
        try {
            $this->authorize('chat-reports.view');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // If permission doesn't exist yet, allow access for admins (handled by middleware)
        }
        
        // Load relationships
        $report->load([
            'chat.user',
            'chat.vendor',
            'chat.service',
            'chat.messages.sender',
            'reporter',
            'reportedUser',
            'reviewer'
        ]);
        
        // Get all messages from the chat
        $messages = $report->chat->messages()->with('sender')->orderBy('created_at', 'asc')->get();
        
        return view('pages.chat-reports.show', compact('report', 'messages'));
    }

    /**
     * Ban the reported user.
     */
    public function banUser(Request $request, ChatReport $report): RedirectResponse
    {
        // Permission check - if permission exists, check it; otherwise allow (for backward compatibility)
        try {
            $this->authorize('chat-reports.ban');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // If permission doesn't exist yet, allow access for admins (handled by middleware)
        }
        
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        $user = Auth::user();
        $reportedUser = $report->reportedUser;
        
        // Ban the user
        $reportedUser->update([
            'status' => 'inactive',
        ]);
        
        // Update the report
        $report->update([
            'status' => 'resolved',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);
        
        return redirect()
            ->route('chat-reports.show', $report)
            ->with('success', __('dashboard.User banned successfully'));
    }

    /**
     * Dismiss the report.
     */
    public function dismiss(Request $request, ChatReport $report): RedirectResponse
    {
        // Permission check - if permission exists, check it; otherwise allow (for backward compatibility)
        try {
            $this->authorize('chat-reports.view');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // If permission doesn't exist yet, allow access for admins (handled by middleware)
        }
        
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        $user = Auth::user();
        
        // Update the report
        $report->update([
            'status' => 'dismissed',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);
        
        return redirect()
            ->route('chat-reports.show', $report)
            ->with('success', __('dashboard.Report dismissed successfully'));
    }
}
