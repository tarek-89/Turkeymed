<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /** RSS 2.0 feed of the latest published posts in the default language. */
    public function index(): Response
    {
        $posts = Post::published()
            ->with('category')
            ->language(Post::DEFAULT_LANGUAGE)
            ->orderByDesc('published_at')
            ->limit(20)
            ->get();

        return response()
            ->view('feed', ['posts' => $posts])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
