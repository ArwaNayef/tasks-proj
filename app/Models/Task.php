<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'schedule_date',
        'is_completed',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

   /* public function comments()
    {
        return $this->hasMany(Comment::class);
    }*/

    public function comments()
    {
        return $this->hasMany(Comment::class)->where('parent_id', null);
    }

    public function commentsWithReplies()
    {
        return $this->comments()->with('replies.replies');
    }
    public function images()
    {
        return $this->morphOne(Image::class, 'imageSource');
    }
}
