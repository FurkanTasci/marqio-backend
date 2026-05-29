<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RssSourceUser extends Pivot
{
    protected $table = 'rss_source_user';

    protected $fillable = [
        'user_id',
        'rss_source_id',
        'subscribed_at',
        'is_active',
        'name',
    ];
}
