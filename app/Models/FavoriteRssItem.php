<?php

namespace App\Models;

use App\Models\RssSource;
use Illuminate\Database\Eloquent\Model;

class FavoriteRssItem extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'item_url',
        'published_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function rssSource()
    {
        return $this->belongsTo(RssSource::class);
    }
}
