<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
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
      'name' => $this->name,
      'email' => $this->email,
      'type' => $this->type,
      'status' => $this->status,
      'phone' => $this->phone,
      'address' => $this->address,
      'profile_picture' => $this->profile_picture ? url(Storage::url($this->profile_picture)) : null,
      'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
      'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
      'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
      'vendor_profile' => $this->when(
        $this->type === 'vendor',
        fn() => VendorProfileResource::make($this->whenLoaded('vendorProfile'))
      ),
    ];
  }
}
