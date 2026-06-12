<?php

namespace App\Models;

use App\Models\FavoriteRssItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RssSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
    ];

    public function favoriteRssItems()
    {
        return $this->hasMany(FavoriteRssItem::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'rss_source_user')
            ->withPivot('subscribed_at', 'is_active', 'name')  // Pivot-Felder hier angeben
            ->withTimestamps();
    }
}
