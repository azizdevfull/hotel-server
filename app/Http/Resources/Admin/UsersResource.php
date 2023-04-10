<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        if($this->hotel_number <= 0 || $this->blocked > 0 ){
            $status = 'deactive';
        }else{
            $status = 'active';
        }
        if($this->blocked > 0){
            $blocked = "Blocked";
        }else{
            $blocked = "Unblocked";
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
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'avatar' => $this->avatar,
            'views' => $this->views,
            'blocked' => $blocked,
        ];
    }
}
