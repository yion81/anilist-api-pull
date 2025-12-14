<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AniListController;

Route::get('/', [AniListController::class, 'index']);

// Route to get the controllers
// search / index.blade
Route::get('/search', [AniListController::class, 'searchView']);
Route::post('/search', [AniListController::class, 'search'])->name('anilist.search');

//mangaprogress / manga.blade
Route::get('/myMangaProgress', [AniListController::class, 'myMangaProgress']);