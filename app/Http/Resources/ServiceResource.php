<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
      'title' => $this->title,
      'description' => $this->description,
      'price_type' => $this->price_type,
      'price' => $this->price ? (float) $this->price : null,
      'status' => $this->status,
      'admin_status' => $this->admin_status,
      'is_visible' => $this->isVisible(),
      'attributes' => $this->attributes ?? [],
      'published_at' => optional($this->published_at)->format('Y-m-d H:i:s'),
      'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
      'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),

      // Relations
      'category' => $this->whenLoaded('category', function () {
        return [
          'id' => $this->category->id,
          'name' => $this->category->name,
        ];
      }),
      'vendor' => $this->whenLoaded('vendor', function () {
        return [
          'id' => $this->vendor->id,
          'name' => $this->vendor->name,
          'profile_picture' => $this->vendor->profile_picture ? url(Storage::url($this->vendor->profile_picture)) : null,
        ];
      }),
      'images' => MediaResource::collection($this->whenLoaded('images')),
      'videos' => MediaResource::collection($this->whenLoaded('videos')),
      'primary_image' => $this->when(
        $this->relationLoaded('images'),
        fn() => $this->primaryImage() ? new MediaResource($this->primaryImage()) : null
      ),
      // Ratings
      'rating' => (float) ($this->reviews_avg_rating ?? 0),
      'reviews_count' => (int) ($this->reviews_count ?? 0),
      'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
    ];
  }
}
