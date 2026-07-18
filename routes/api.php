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
    Route::get('/bookmarks/search', [BookmarkController::class, 'search']);
    Route::get('/bookmarks/export', [BookmarkController::class, 'exportHtml'])->middleware('throttle:5,1');
    Route::post('/bookmarks', [BookmarkController::class, 'store']);
    Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy']);
    Route::patch('/bookmarks/{bookmark}', [BookmarkController::class, 'update']);
    Route::patch('/bookmarks/{bookmark}/tags', [TagsController::class, 'update']);

    Route::get('/rss-feed', [RssController::class, 'getUserFeeds']);
    Route::get('/rss-feed/{sourceId}', [RssController::class, 'getSourceFeeds']);
    Route::get('/rss-top', [RssSourceController::class, 'getCatalog']);
    Route::post('/rss-sources/{id}/subscribe', [RssSourceController::class, 'subscribe']);
    Route::patch('/rss-sources/{id}/unsubscribe', [RssSourceController::class, 'unsubscribe']);
    Route::get('/rss-sources', [RssSourceController::class, 'index']);
    Route::post('/rss-sources', [RssSourceController::class, 'store']);
    Route::get('/rss-sources/{id}', [RssSourceController::class, 'show']);
    Route::put('/rss-sources/{id}', [RssSourceController::class, 'update']);
    Route::delete('/rss-sources/{id}', [RssSourceController::class, 'destroy']);

    Route::get('/favorite-rss-items', [FavoriteRssItemController::class, 'index']);
    Route::post('/favorite-rss-items', [FavoriteRssItemController::class, 'store']);
    Route::delete('/favorite-rss-items/{id}', [FavoriteRssItemController::class, 'destroy']);

    Route::post('/import-opml', [OpmlController::class, 'import']);
});

