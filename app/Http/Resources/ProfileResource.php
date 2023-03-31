<?php

namespace App\Http\Resources;

use App\Models\AdminUserCategory;
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

        if($this->hotel_number <= 0 ){
            $status = 'deactive';
        }else{
            $status = 'active';
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
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

        ];
    }
}