<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'type' => $this->type,
      'url' => $this->path ? url(Storage::url($this->path)) : null,
      'mime_type' => $this->mime_type,
      'size' => $this->size,
      'order' => $this->order,
      'is_primary' => $this->is_primary,
      'meta' => $this->meta ?? [],
      'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
    ];
  }
}
