<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VendorProfileResource extends JsonResource
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
      'country_id' => $this->country_id,
      'country' => $this->whenLoaded('country', function () {
        return [
          'id' => $this->country->id,
          'name' => $this->country->name,
        ];
      }),
      'city_id' => $this->city_id,
      'city' => $this->whenLoaded('city', function () {
        return [
          'id' => $this->city->id,
          'name' => $this->city->name,
        ];
      }),
      'lat' => isset($this->lat) ? (float) $this->lat : null,
      'lng' => isset($this->lng) ? (float) $this->lng : null,
      'banner' => $this->banner,
      'banner_url' => $this->banner ? url(Storage::url($this->banner)) : null,
      'bio' => $this->bio,
      'meta' => $this->meta ?? [],
      'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
      'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
    ];
  }
}

