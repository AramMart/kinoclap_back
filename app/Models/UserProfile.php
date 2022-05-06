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
        'profile_image',
        'resume_file'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        return $this->belongsToMany(Resource::class);
    }
}
