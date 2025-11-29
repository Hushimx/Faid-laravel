<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ServiceFaq extends Model
{
    use HasTranslations;

    /**
     * The attributes that can be mass-assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
        'question',
        'answer',
        'order',
    ];

    /**
     * The attributes that support translations.
     *
     * @var array<int, string>
     */
    public array $translatable = [
        'question',
        'answer',
    ];

    /**
     * Service relation.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
