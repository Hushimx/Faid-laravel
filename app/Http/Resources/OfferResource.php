<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OfferResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   */
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'image' => $this->image ? url(Storage::url($this->image)) : null,
      'created_at' => $this->created_at?->toDateTimeString(),
    ];
  }
}
