<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendNotificationRequest;
use App\Models\AdminNotification as Notification;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Display notifications history.
     */
    public function index(Request $request)
    {
        $query = Notification::with('admin')->latest();

        // Filter by target type
        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->paginate(20);

        // Statistics
        $stats = [
            'total_notifications' => Notification::count(),
            'total_sent' => Notification::sum('sent_count'),
            'total_failed' => Notification::sum('failed_count'),
            'success_rate' => 0,
        ];

        $totalAttempts = $stats['total_sent'] + $stats['total_failed'];
        if ($totalAttempts > 0) {
            $stats['success_rate'] = round(($stats['total_sent'] / $totalAttempts) * 100, 2);
        }

        return view('pages.notifications', compact('notifications', 'stats'));
    }

    /**
     * Show create notification form.
     */
    public function create()
    {
        // Get all users for individual selection
        $users = User::select('id', 'first_name', 'last_name', 'email', 'type')
            ->whereIn('type', ['user', 'vendor'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'email' => $user->email,
                    'type' => $user->type,
                ];
            });

        return view('pages.notifications-create', compact('users'));
    }

    /**
     * Send notification.
     */
    public function store(SendNotificationRequest $request)
    {
        try {
            DB::beginTransaction();

            // Prepare custom data
            $customData = [];
            if ($request->filled('custom_data')) {
                $customData = json_decode($request->custom_data, true) ?? [];
            }

            // Determine target value
            $targetValue = null;
            if ($request->target_type === 'role') {
                $targetValue = ['role' => $request->target_role];
            } elseif ($request->target_type === 'individual') {
                $targetValue = $request->target_users;
            }

            // Create notification record
            $notification = Notification::create([
                'admin_id' => auth()->id(),
                'title' => [
                    'ar' => $request->title_ar,
                    'en' => $request->title_en,
                ],
                'body' => [
                    'ar' => $request->body_ar,
                    'en' => $request->body_en,
                ],
                'target_type' => $request->target_type,
                'target_value' => $targetValue,
                'data' => $customData,
            ]);

            // Send notification based on target type
            $result = match ($request->target_type) {
                'all' => $this->firebaseService->sendToAllUsers(
                    $request->title_ar,
                    $request->title_en,
                    $request->body_ar,
                    $request->body_en,
                    $customData
                ),
                'role' => $this->firebaseService->sendToUserType(
                    $request->target_role,
                    $request->title_ar,
                    $request->title_en,
                    $request->body_ar,
                    $request->body_en,
                    $customData
                ),
                'individual' => $this->firebaseService->sendToUsers(
                    $request->target_users,
                    $request->title_ar,
                    $request->title_en,
                    $request->body_ar,
                    $request->body_en,
                    $customData
                ),
                default => ['success' => 0, 'failure' => 0],
            };

            // Update notification with results
            $notification->update([
                'sent_count' => $result['success'],
                'failed_count' => $result['failure'],
            ]);

            DB::commit();

            return redirect()
                ->route('notifications.index')
                ->with('success', __('dashboard.Notification sent successfully') . ': ' . $result['success'] . ' ' . __('dashboard.sent') . ', ' . $result['failure'] . ' ' . __('dashboard.failed'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send notification: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('dashboard.Failed to send notification') . ': ' . $e->getMessage());
        }
    }

    /**
     * Show notification details.
     */
    public function show(Notification $notification)
    {
        $notification->load('admin');
        return view('pages.notifications-show', compact('notification'));
    }
}
