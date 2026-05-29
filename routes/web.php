<?php

use App\Http\Controllers\API\BookmarkController;
use App\Http\Controllers\API\RssController;
use App\Http\Controllers\API\RssSourceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    /*
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
    */
    Route::get('dashboard', [RssController::class, 'getUserFeeds']);
    Route::get('bookmarks', [BookmarkController::class, 'index']);
    Route::get('rss-sources', [RssSourceController::class, 'index']);
});

require __DIR__.'/settings.php';
