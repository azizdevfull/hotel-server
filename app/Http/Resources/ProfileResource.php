<?php

namespace App\Http\Resources;

use App\Models\AdminUserCategory;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if($this->hotel_number <= 0 || $this->blocked > 0 ){
            if(App::isLocale('ru')){
                $status = 'Деактивировано';
            }else{
                $status = 'O\'chirilgan';
            }
        }else{
            if(App::isLocale('ru')){
                $status = 'Активный';
            }else{
                $status = 'Faol';
            }
        }

        if($this->blocked > 0){
            if(App::isLocale('ru')){
                $blocked = 'Заблокировано';
            }else{
                $blocked = 'Bloklangan';
            }
        }else{
            if(App::isLocale('ru')){
                $blocked = 'Разблокировано';
            }else{
                $blocked = 'Blokdan chiqarildi';
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->phone,
            'status' => $status,
            'hotel_number' => $this->hotel_number,
            'phone_verified_at' => $this->phone_verified_at,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'views' => $this->views,
            'blocked' => $blocked,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

        ];
    }
}