<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReviewResource extends JsonResource
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
      'rating' => (int) $this->rating,
      'comment' => $this->comment,
      'user' => $this->whenLoaded('user', function () {
        return [
          'id' => $this->user->id,
          'name' => $this->user->name,
          'profile_picture' => $this->user->profile_picture ? url(Storage::url($this->user->profile_picture)) : null,
        ];
      }),
      'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
    ];
  }
}
