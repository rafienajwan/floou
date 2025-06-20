<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category'),
            'plant_type_id' => $this->plant_type_id,
            'plant_type' => $this->whenLoaded('plantType'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
