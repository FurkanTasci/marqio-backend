<?php

namespace App\Models;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'description',
        'image',
        'is_favorite',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Überschreibt das Default message von laravel 
     *  => "message": "No query results for model ..."
     */
    public function resolveRouteBinding($value, $fieId = null)
    {
        $bookmark = $this->where($fieId ?? $this->getRouteKeyName(), $value)->first();

        if (!$bookmark) {
            abort(response()->json([
                'message' => 'Bookmark does not exist'
            ]), 404);
        }

        if ($bookmark->user_id !== auth()->id()) {
            abort(response()->json([
                'message' => 'This action is unauthorized.'
            ]), 401);
        }

        return $bookmark;
    }
}
