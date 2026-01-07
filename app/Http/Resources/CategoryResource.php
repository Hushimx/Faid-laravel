<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    // Spatie's HasTranslations automatically returns the translated value
    // based on the current locale when accessing the attribute directly
    return [
      'id' => $this->id,
      'name' => $this->name ?: '',
      'description' => $this->description ?: '',
      'image_url' => $this->image ? url(Storage::url($this->image)) : null,
      'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
      'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
    ];
  }
}
