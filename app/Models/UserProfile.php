<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'phone_number',
        'phone_code',
        'profile_image',
        'is_casting',
        'resource_id',
        'resume_file',
        'profession_id',
        'country_id',
        'gender',
        'age',
        'approved'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo {
        return $this->belongsTo(Country::class);
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }

    public function profileImage(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'profile_image');
    }

    public function resumeFile(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resume_file');
    }

    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'profile_resources','profile_id','resource_id');
    }
}
