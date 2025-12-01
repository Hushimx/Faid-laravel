<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class Service extends Model
{
    use HasTranslations;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const ADMIN_STATUS_SUSPENDED = 'suspended';

    public const PRICE_TYPE_FIXED = 'fixed';
    public const PRICE_TYPE_NEGOTIABLE = 'negotiable';

    /**
     * The attributes that can be mass-assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'vendor_id',
        'title',
        'description',
        'price_type',
        'price',
        'address',
        'city',
        'status',
        'admin_status',
        'attributes',
        'published_at',
    ];

    /**
     * The attributes that support translations.
     *
     * @var array<int, string>
     */
    public array $translatable = [
        'title',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    /**
     * Category relation.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Vendor relation.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Media relation (polymorphic).
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order')->orderBy('created_at');
    }

    /**
     * Reviews relation.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class)->orderBy('created_at', 'desc');
    }

    /**
     * FAQs relation.
     */
    public function faqs()
    {
        return $this->hasMany(ServiceFaq::class)->orderBy('order')->orderBy('id');
    }


    /**
     * Average rating (float) for the service.
     */
    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');
        return $avg !== null ? (float) number_format((float) $avg, 2) : null;
    }

    /**
     * Reviews count.
     */
    public function reviewsCount(): int
    {
        return (int) $this->reviews()->count();
    }

    /**
     * Images relation.
     */
    public function images(): MorphMany
    {
        return $this->media()->where('type', 'image');
    }

    /**
     * Videos relation.
     */
    public function videos(): MorphMany
    {
        return $this->media()->where('type', 'video');
    }

    /**
     * Primary image.
     */
    public function primaryImage()
    {
        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();
    }

    /**
     * Check if service is visible to public.
     */
    public function isVisible(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->admin_status !== self::ADMIN_STATUS_SUSPENDED;
    }

    /**
     * Available statuses for vendor.
     *
     * @return array<int, string>
     */
    public static function vendorStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
        ];
    }

    /**
     * Available price types.
     *
     * @return array<int, string>
     */
    public static function priceTypes(): array
    {
        return [
            self::PRICE_TYPE_FIXED,
            self::PRICE_TYPE_NEGOTIABLE,
        ];
    }
}
