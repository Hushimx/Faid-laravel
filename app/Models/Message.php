<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'chat_id', 'sender_id', 'message_type', 'message', 'file_path', 'file_type'
    ];

    public function chat() {
        return $this->belongsTo(Chat::class);
    }
}
