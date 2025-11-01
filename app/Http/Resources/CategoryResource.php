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
    return [
      'id' => $this->id,
      'name' => $this->name,
      'description' => $this->description,
      'image_url' => $this->image ? url(Storage::url($this->image)) : null,
      'parent_id' => $this->parent_id,
      'parent' => $this->when(
        $this->relationLoaded('parent') && $this->parent !== null,
        function () {
          return [
            'id' => $this->parent->id,
            'name' => $this->parent->name,
          ];
        }
      ),
      'children_count' => $this->whenCounted('children'),
      'children' => CategoryResource::collection($this->whenLoaded('children')),
      'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
      'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
    ];
  }
}
