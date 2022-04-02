<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'type', 'sub_category_id', 'active'
    ];

    public function resources()
    {
        return $this->belongsToMany(Resource::class);
    }
}
