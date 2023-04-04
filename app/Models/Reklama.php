<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reklama extends Model
{
    use HasFactory;

    protected $fillable = ['images'];

    public function imagesReklama()
    {
        return $this->hasMany(ImagesReklama::class);
    }
}
