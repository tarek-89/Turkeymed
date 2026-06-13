<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes — WordPress-compatible URL structure
|--------------------------------------------------------------------------
| English (default language): no prefix    ->  /{slug}
| Other languages: locale prefix           ->  /{locale}/{slug}
|
| Order matters: the bare /{slug} route is a catch-all and must be last.
| {locale} is constrained to 2-letter codes so it never swallows slugs.
|
| Services and posts share the same /{slug} namespace (matching WordPress).
| The catch-all controller tries posts first, then services, then 404s.
*/

Route::pattern('locale', '[a-z]{2}');
Route::pattern('slug', '[^/]+');

// Marketing homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Blog index (moved off the root, which is now the homepage)
Route::get('/blog', [PostController::class, 'index'])->name('posts.index');

// Category routes (explicit prefix, so they don't clash with the catch-all)
Route::get('/category/{categorySlug}', [ServiceController::class, 'category'])->name('services.category');
Route::get('/{locale}/category/{categorySlug}', [ServiceController::class, 'categoryLocalized'])->name('services.category.localized');

// About page (explicit path, before the catch-alls)
Route::get('/about', [AboutController::class, 'show'])->name('about');
Route::get('/{locale}/about', [AboutController::class, 'showLocalized'])->name('about.localized');

// Contact page (explicit path, before the catch-alls)
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::get('/{locale}/contact', [ContactController::class, 'showLocalized'])->name('contact.localized');

// Localized blog index (before the localized catch-all)
Route::get('/{locale}/blog', [PostController::class, 'indexLocalized'])->name('posts.index.localized');

// Localized homepage
Route::get('/{locale}', [HomeController::class, 'indexLocalized'])->name('home.localized');

// Localized catch-all: tries post first, then service
Route::get('/{locale}/{slug}', [PostController::class, 'showLocalized'])->name('posts.show.localized');

// Catch-all: English posts/services at the root. Keep this LAST.
Route::get('/{slug}', [PostController::class, 'show'])->name('posts.show');
