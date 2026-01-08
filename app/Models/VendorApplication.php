<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorApplication extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'city',
        'country_id',
        'city_id',
        'lat',
        'lng',
        'banner',
        'bio',
        'category_id',
        'custom_category',
        'meta',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'reviewed_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}


