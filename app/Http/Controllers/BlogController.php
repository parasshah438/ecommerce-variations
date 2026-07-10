<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Artesaos\SEOTools\Facades\SEOTools;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::published()->with('category');

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $posts = $query->orderBy('published_at', 'desc')->paginate(12);
        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();
        $trendingPosts = BlogPost::published()->trending()->orderBy('views_count', 'desc')->take(5)->get();

        SEOTools::setTitle(config('app.name') . ' - Blog')
            ->setDescription('Read our latest articles, guides, and tips about fashion and lifestyle.')
            ->setCanonical(url()->current());

        return view('blog.index', compact('posts', 'categories', 'trendingPosts'));
    }

    public function show(BlogPost $blogPost)
    {
        if ($blogPost->status !== 'published') {
            abort(404);
        }

        $blogPost->incrementViews();

        SEOTools::setTitle($blogPost->meta_title)
            ->setDescription($blogPost->meta_description)
            ->setCanonical($blogPost->canonical_url ?: url()->current());

        if ($blogPost->meta_keywords) {
            SEOTools::metas()->setKeywords(explode(',', $blogPost->meta_keywords));
        }

        SEOTools::opengraph()
            ->setTitle($blogPost->meta_title)
            ->setDescription($blogPost->meta_description)
            ->setUrl(url()->current())
            ->setType('article')
            ->addImage($blogPost->og_image_url);

        if ($blogPost->published_at) {
            SEOTools::opengraph()->addProperty('article:published_time', $blogPost->published_at->toIso8601String());
        }

        SEOTools::twitter()
            ->setTitle($blogPost->meta_title)
            ->setDescription($blogPost->meta_description)
            ->setImage($blogPost->og_image_url);

        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $blogPost->id)
            ->when($blogPost->blog_category_id, fn($q) => $q->where('blog_category_id', $blogPost->blog_category_id))
            ->orderBy('views_count', 'desc')
            ->take(4)
            ->get();

        $trendingPosts = BlogPost::published()->trending()
            ->where('id', '!=', $blogPost->id)
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        return view('blog.show', compact('blogPost', 'relatedPosts', 'trendingPosts'));
    }

    public function category(BlogCategory $blogCategory)
    {
        $posts = BlogPost::published()
            ->where('blog_category_id', $blogCategory->id)
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        SEOTools::setTitle($blogCategory->meta_title ?: $blogCategory->name . ' - ' . config('app.name'))
            ->setDescription($blogCategory->meta_description)
            ->setCanonical(url()->current());

        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();
        $trendingPosts = BlogPost::published()->trending()->orderBy('views_count', 'desc')->take(5)->get();

        return view('blog.index', compact('posts', 'categories', 'trendingPosts'))
            ->with('activeCategory', $blogCategory);
    }
}
