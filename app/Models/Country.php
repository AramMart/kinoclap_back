<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_am', 'name_ru', 'name_en', 'phone_code'
    ];

    public function profiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
