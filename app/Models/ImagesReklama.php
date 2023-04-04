<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagesReklama extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'reklama_id', 'url'];

    public function reklama()
    {
        return $this->belongsTo(Reklama::class);
    }
}
