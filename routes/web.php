<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes — foldered URL structure
|--------------------------------------------------------------------------
| English (default language): no prefix    ->  /blog/{category}/{slug}
| Other languages: locale prefix           ->  /{locale}/blog/{category}/{slug}
|
| Blog posts live under /blog, services under /services, each nested beneath
| their category. Content with no category uses the literal "uncategorized"
| segment. {locale} is constrained to 2-letter codes so it never swallows a
| more specific path. Old WordPress URLs are 301-redirected by the
| redirects table (see HandleRedirects middleware).
*/

Route::pattern('locale', '[a-z]{2}');
Route::pattern('categorySlug', '[^/]+');
Route::pattern('slug', '[^/]+');

// Marketing homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// robots.txt — dynamic: respects SITE_INDEXABLE and uses the current host for
// the sitemap URL. The static public/robots.txt was removed so this is reached.
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

// XML sitemap (explicit path, before any catch-all)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// RSS feed of the latest blog posts
Route::get('/feed.xml', [FeedController::class, 'index'])->name('feed');

/*
|--------------------------------------------------------------------------
| English (default language)
|--------------------------------------------------------------------------
*/

// Blog
Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
Route::get('/blog/{categorySlug}', [PostController::class, 'category'])->name('posts.category');
Route::get('/blog/{categorySlug}/{slug}', [PostController::class, 'show'])->name('posts.show');

// Services
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{categorySlug}', [ServiceController::class, 'category'])->name('services.category');
Route::get('/services/{categorySlug}/{slug}', [ServiceController::class, 'show'])->name('services.show');

// Static pages
Route::get('/about', [AboutController::class, 'show'])->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');

/*
|--------------------------------------------------------------------------
| Localized (/{locale}/...) — most specific first, bare /{locale} last
|--------------------------------------------------------------------------
*/

// Blog
Route::get('/{locale}/blog', [PostController::class, 'indexLocalized'])->name('posts.index.localized');
Route::get('/{locale}/blog/{categorySlug}', [PostController::class, 'categoryLocalized'])->name('posts.category.localized');
Route::get('/{locale}/blog/{categorySlug}/{slug}', [PostController::class, 'showLocalized'])->name('posts.show.localized');

// Services
Route::get('/{locale}/services', [ServiceController::class, 'indexLocalized'])->name('services.index.localized');
Route::get('/{locale}/services/{categorySlug}', [ServiceController::class, 'categoryLocalized'])->name('services.category.localized');
Route::get('/{locale}/services/{categorySlug}/{slug}', [ServiceController::class, 'showLocalized'])->name('services.show.localized');

// Static pages
Route::get('/{locale}/about', [AboutController::class, 'showLocalized'])->name('about.localized');
Route::get('/{locale}/contact', [ContactController::class, 'showLocalized'])->name('contact.localized');

// Localized homepage (keep last — bare 2-letter segment)
Route::get('/{locale}', [HomeController::class, 'indexLocalized'])->name('home.localized');
