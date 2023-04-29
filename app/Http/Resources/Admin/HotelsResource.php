<?php

namespace App\Http\Resources\Admin;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
        $regions = Region::all();

        foreach ( $regions as $region ){
            if($region->id == $this->region_id){
                if(App::isLocale('ru')){
                    $region_id = $region->rus_name;
                }else{
                    $region_id = $region->name;
                }
            }else{
                $region_id = null;
            }
        }
        
      
        if($this->category){
            $category_name = $this->category->name;
            
                    if(App::isLocale('ru')){
                        $category_name = $this->category->rus_name;
                    }else{
                        $category_name = $this->category->name;
                    }
        }else{
            $category_name = null;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
            'category' => $category_name,
            'user' => $this->user->username,
            'region' => $region_id,
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
