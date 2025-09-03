<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder;

class Post extends Model
{
    //
    use HasFactory;
    protected $fillable = ['title', 'content','slug','category','is_active'];
    protected $table = 'posts';

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }


    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public static function booted(): void
    // {
    //     static::addGlobalScope('is_active', function(Builder $builder){
    //         $builder->where('is_active', true);
    //     });
    // }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

}
