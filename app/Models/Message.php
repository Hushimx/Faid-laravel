<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    protected $fillable = [
        'chat_id', 'sender_id', 'message_type', 'message', 'file_path', 'file_type', 'latitude', 'longitude'
    ];

    public function getFilePathAttribute($value) {
        return $value ? url(Storage::url($value)) : null;
    }

    public function chat() {
        return $this->belongsTo(Chat::class);
    }
}
