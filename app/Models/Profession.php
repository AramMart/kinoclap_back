<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_am', 'name_ru', 'name_en'
    ];

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
