<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    use HasFactory;

    protected $table = 'admin_notifications';

    protected $fillable = [
        'admin_id',
        'title',
        'body',
        'target_type',
        'target_value',
        'sent_count',
        'failed_count',
        'data',
    ];

    protected $casts = [
        'title' => 'array',
        'body' => 'array',
        'target_value' => 'array',
        'data' => 'array',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    /**
     * Get the admin who sent the notification.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the title in the specified language.
     */
    public function getTitleInLanguage(string $lang = null): string
    {
        $lang = $lang ?? app()->getLocale();
        return $this->title[$lang] ?? $this->title['en'] ?? '';
    }

    /**
     * Get the body in the specified language.
     */
    public function getBodyInLanguage(string $lang = null): string
    {
        $lang = $lang ?? app()->getLocale();
        return $this->body[$lang] ?? $this->body['en'] ?? '';
    }

    /**
     * Get success rate as percentage.
     */
    public function getSuccessRateAttribute(): float
    {
        $total = $this->sent_count + $this->failed_count;
        if ($total === 0) {
            return 0;
        }
        return round(($this->sent_count / $total) * 100, 2);
    }

    /**
     * Get formatted target display text.
     */
    public function getTargetDisplayAttribute(): string
    {
        return match ($this->target_type) {
            'all' => __('dashboard.All Users'),
            'role' => __('dashboard.Role') . ': ' . ($this->target_value['role'] ?? ''),
            'individual' => __('dashboard.Individual Users') . ' (' . count($this->target_value ?? []) . ')',
            default => $this->target_type,
        };
    }
}
