<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'type',
        'profile_picture',
        'phone',
        'address',
        'status',
        'otp',
        'otp_expires_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The vendor profile associated with the user.
     */
    public function vendorProfile()
    {
        return $this->hasOne(VendorProfile::class);
    }

    /**
     * Services created by vendor.
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'vendor_id');
    }

    /**
     * Products created by vendor.
     */
    // public function products()
    // {
    //     return $this->hasMany(Product::class, 'vendor_id');
    // }

    /**
     * Get the full name attribute (for backward compatibility).
     */
    public function getNameAttribute($value)
    {
        // If name exists in database (old records), return it
        if (isset($this->attributes['name']) && $this->attributes['name']) {
            return $this->attributes['name'];
        }

        // Otherwise, compute from first_name and last_name
        if ($this->attributes['first_name'] ?? null) {
            $firstName = $this->attributes['first_name'];
            $lastName = $this->attributes['last_name'] ?? null;

            if ($lastName) {
                return trim($firstName . ' ' . $lastName);
            }
            return $firstName;
        }

        return '';
    }

    /**
     * Tickets created by user.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Tickets assigned to admin.
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Messages sent by user.
     */
    public function ticketMessages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    /**
     * FCM tokens associated with the user.
     */
    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    /**
     * Active FCM tokens only.
     */
    public function activeFcmTokens()
    {
        return $this->hasMany(FcmToken::class)->where('is_active', true);
    }

    /**
     * Notifications sent by admin.
     */
    public function sentNotifications()
    {
        return $this->hasMany(Notification::class, 'admin_id');
    }

    /**
     * Vendor application submitted by user.
     */
    public function vendorApplication()
    {
        return $this->hasOne(VendorApplication::class);
    }
}
