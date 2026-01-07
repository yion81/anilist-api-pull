<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AniListController;

Route::get('/', [AniListController::class, 'index']);

// Route to get the controllers
// search / index.blade
Route::get('/search', [AniListController::class, 'searchView']);
Route::post('/search', [AniListController::class, 'processSearch'])->name('anilist.search');
Route::get('/search/{username}', [AniListController::class, 'showResult'])->name('anilist.result');


//mangaprogress / manga.blade
Route::get('/myMangaProgress', [AniListController::class, 'myMangaProgress']);

Route::get('/tags', [AniListController::class, 'tagsView']);
Route::post('/tags', [AniListController::class, 'processTagSearch'])->name('anilist.tags_process');
Route::get('/tags/{username}', [AniListController::class, 'showTags'])->name('anilist.tags_result');