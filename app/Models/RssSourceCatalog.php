<?php

namespace App\Models;

use App\Models\RssSource;
use Illuminate\Database\Eloquent\Model;

class RssSourceCatalog extends Model
{
    protected $table = 'rss_source_catalog';
    
    protected $fillable = [
        'rss_source_id',
        'country',
        'category',
        'rank',
        'is_featured'
    ];

    public function source()
    {
        return $this->belongsTo(RssSource::class, 'rss_source_id');
    }
}
