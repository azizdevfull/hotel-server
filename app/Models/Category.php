<?php

namespace App\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'rus_name'];

    public function hotels(){
        return $this->hasMany(Hotel::class);
    }
}
