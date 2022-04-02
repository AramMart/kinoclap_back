<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'path', 'type'
    ];

    public function getPathAttribute($value)
    {
        return Storage::disk('gcs')->url($value);
    }
}
