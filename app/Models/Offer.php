<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offer extends Model
{
  use HasFactory;

  protected $fillable = [
    'image',
    'status',
  ];

  public const STATUS_ACTIVE = 'active';
  public const STATUS_INACTIVE = 'inactive';

  public static function statuses(): array
  {
    return [self::STATUS_ACTIVE, self::STATUS_INACTIVE];
  }
}
