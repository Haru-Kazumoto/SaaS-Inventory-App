<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'fullname',
        'phone',
        'email',
        'password',
        'role_id',
        'major_id',
        'profile_image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getProfileImageUrlAttribute(): string 
    {
        return $this->profile_image ? asset('storage/' . $this->profile_image) : asset('images/logo-yatindo-hd.png');
    }


    public function role(): BelongsTo    
    {
        return $this->belongsTo(Role::class);
    }

    public function major(): BelongsTo    
    {
        return $this->belongsTo(Major::class);
    }
}
