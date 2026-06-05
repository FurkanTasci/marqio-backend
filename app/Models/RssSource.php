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
        'title',
        'site_url',
        'country_code',
        'language',
        'category',
        'is_featured',
        'is_public',
        'subscriber_count',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'subscriber_count' => 'integer',
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
    
    /**
     * Optional: Relationship zu User Abos
     */
    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'rss_source_user')
            ->withPivot('name', 'is_active', 'subscribed_at')
             ->withTimestamps();
    }

}
