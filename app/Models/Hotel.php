<?php

namespace App\Models;

use App\Models\User;
use App\Models\Photos;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'description', 'category_id','photos','longitude','latitude'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function photos()
    {
        return $this->hasMany(Photos::class);
    }

    public static function search($term)
    {
        return self::where('name', 'LIKE', "%$term%")->get();
    }
}
