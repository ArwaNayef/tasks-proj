<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Manager extends Model implements JWTSubject
{
    use HasFactory, HasApiTokens;
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function images()
    {
        return $this->morphOne(Image::class, 'imageSource');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
