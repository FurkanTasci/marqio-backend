<?php

use App\Http\Controllers\API\BookmarkController;
use App\Http\Controllers\API\FavoriteRssItemController;
use App\Http\Controllers\API\OpmlController;
use App\Http\Controllers\API\RssController;
use App\Http\Controllers\API\RssSourceController;
use App\Http\Controllers\API\TagsController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Bookmarks
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks', [BookmarkController::class, 'store']);
    Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy']);
    Route::patch('/bookmarks/{bookmark}', [BookmarkController::class, 'update']);
    Route::patch('/bookmarks/{bookmark}/tags', [TagsController::class, 'update']);

    Route::get('/rss-feed', [RssController::class, 'getUserFeeds']);
    Route::get('/rss-feed/{sourceId}', [RssController::class, 'getSourceFeeds']);
    Route::get('/rss-sources', [RssSourceController::class, 'index']);
    Route::post('/rss-sources', [RssSourceController::class, 'store']);
    Route::get('/rss-sources/{id}', [RssSourceController::class, 'show']);
    Route::put('/rss-sources/{id}', [RssSourceController::class, 'update']);
    Route::delete('/rss-sources/{id}', [RssSourceController::class, 'destroy']);
    // 
    Route::get('rss-sources/top', [RssSourceController::class, 'topByCountry']);
    /**
     * @example 
     *  - Top 10 Germany
     *  GET /rss-sources/top?country_code=DE
     * 
     *  - Top Deutschland, only Featured
     *  GET /rss-sources/top?country_code=DE&limit=5&featured_only=1
     * 
     *  - Top 20 USA
     *  GET /rss-sources/top?country_code=US&limit=20
     * 
     *  - Top 5 UK, only Featured
     *  GET /rss-sources/top?country_code=UK&limit=5&
     */


    Route::get('/favorite-rss-items', [FavoriteRssItemController::class, 'index']);
    Route::post('/favorite-rss-items', [FavoriteRssItemController::class, 'store']);
    Route::delete('/favorite-rss-items/{id}', [FavoriteRssItemController::class, 'destroy']);

    Route::post('/import-opml', [OpmlController::class, 'import']);
});

