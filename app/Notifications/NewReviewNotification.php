<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification
{
    use Queueable;

    protected $review;

    /**
     * Create a new notification instance.
     */
    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review',
            'title' => [
                'en' => 'New Review',
                'ar' => 'تقييم جديد'
            ],
            'body' => [
                'en' => "{$this->review->user->name} rated your service {$this->review->service->title}",
                'ar' => "قام {$this->review->user->name} بتقييم خدمتك {$this->review->service->title}"
            ],
            'service_id' => $this->review->service_id,
            'review_id' => $this->review->id,
            'rating' => $this->review->rating,
        ];
    }
}
