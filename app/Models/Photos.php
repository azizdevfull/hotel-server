<?php

namespace App\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Photos extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'hotel_id', 'url'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
