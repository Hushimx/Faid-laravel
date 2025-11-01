<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
  /**
   * The attributes that can be mass-assigned.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'mediable_type',
    'mediable_id',
    'type',
    'path',
    'mime_type',
    'size',
    'order',
    'is_primary',
    'meta',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'meta' => 'array',
    'size' => 'integer',
    'order' => 'integer',
    'is_primary' => 'boolean',
  ];

  /**
   * Get the parent mediable model (service or product).
   */
  public function mediable(): MorphTo
  {
    return $this->morphTo();
  }

  /**
   * Get the full URL for the media.
   */
  public function getUrlAttribute(): string
  {
    return Storage::url($this->path);
  }

  /**
   * Scope for images.
   */
  public function scopeImages($query)
  {
    return $query->where('type', 'image');
  }

  /**
   * Scope for videos.
   */
  public function scopeVideos($query)
  {
    return $query->where('type', 'video');
  }

  /**
   * Scope for primary media.
   */
  public function scopePrimary($query)
  {
    return $query->where('is_primary', true);
  }

  /**
   * Delete the file when model is deleted.
   */
  protected static function booted()
  {
    static::deleting(function ($media) {
      if ($media->path && Storage::exists($media->path)) {
        Storage::delete($media->path);
      }
    });
  }
}
