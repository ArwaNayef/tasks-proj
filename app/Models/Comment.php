<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'task_id',
        'body',
        'parent_id',
        'picture',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
    public function images()
    {
        return $this->morphOne(Image::class, 'imageSource');
    }
}
