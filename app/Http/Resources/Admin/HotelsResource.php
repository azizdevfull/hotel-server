<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelsResource extends JsonResource
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
            'price' => $this->price,
            'description' => $this->description,
            'category' => $this->category->name,
            'user' => $this->user->username,
            'views' => $this->views,
            'stars' => floatval($this->stars),
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'photos' => $this->photos->map(function ($photo) {
                return $photo->url;
            }),
        ];
    }
}