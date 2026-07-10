<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageOptimizer;

class BlogPostController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = BlogPost::with('category');

        if ($request->filled('status') && in_array($request->status, ['published', 'draft', 'archived'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('blog_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = BlogCategory::orderBy('name')->get();

        $stats = [
            'total' => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'drafts' => BlogPost::where('status', 'draft')->count(),
            'trending' => BlogPost::where('is_trending', true)->count(),
            'total_views' => BlogPost::sum('views_count'),
        ];
        return view('admin.blog.posts.index', compact('posts', 'stats', 'categories'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'author' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'is_trending' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'canonical_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['title']);
        $validated['is_trending'] = $request->boolean('is_trending');
        $validated['is_featured'] = $request->boolean('is_featured');

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $result = ImageOptimizer::handleUpload(
                $request->file('featured_image'),
                'blog',
                [
                    'quality' => 80,
                    'max_width' => 1200,
                    'max_height' => 630,
                    'generate_webp' => true,
                    'thumbnails' => [300, 600],
                ]
            );
            $validated['featured_image'] = $result['path'] ?? null;
        }

        // Handle OG image
        if ($request->hasFile('og_image')) {
            $result = ImageOptimizer::handleUpload(
                $request->file('og_image'),
                'blog/og',
                [
                    'quality' => 80,
                    'max_width' => 1200,
                    'max_height' => 630,
                    'generate_webp' => true,
                ]
            );
            $validated['og_image'] = $result['path'] ?? null;
        }

        $validated['published_at'] = $validated['published_at']
            ?? ($validated['status'] === 'published' ? now() : null);

        BlogPost::create($validated);

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Blog post created successfully.');
    }

    public function edit(BlogPost $blogPost)
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog.posts.edit', compact('blogPost', 'categories'));
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $blogPost->id,
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'author' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'is_trending' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'canonical_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['title']);
        $validated['is_trending'] = $request->boolean('is_trending');
        $validated['is_featured'] = $request->boolean('is_featured');

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            if ($blogPost->featured_image) {
                Storage::disk('public')->delete($blogPost->featured_image);
            }
            $result = ImageOptimizer::handleUpload(
                $request->file('featured_image'),
                'blog',
                [
                    'quality' => 80,
                    'max_width' => 1200,
                    'max_height' => 630,
                    'generate_webp' => true,
                    'thumbnails' => [300, 600],
                ]
            );
            $validated['featured_image'] = $result['path'] ?? null;
        } else {
            $validated['featured_image'] = $blogPost->featured_image;
        }

        // Handle OG image
        if ($request->hasFile('og_image')) {
            if ($blogPost->og_image) {
                Storage::disk('public')->delete($blogPost->og_image);
            }
            $result = ImageOptimizer::handleUpload(
                $request->file('og_image'),
                'blog/og',
                [
                    'quality' => 80,
                    'max_width' => 1200,
                    'max_height' => 630,
                    'generate_webp' => true,
                ]
            );
            $validated['og_image'] = $result['path'] ?? null;
        } else {
            $validated['og_image'] = $blogPost->og_image;
        }

        if ($validated['status'] === 'published' && empty($blogPost->published_at)) {
            $validated['published_at'] = $validated['published_at'] ?? now();
        }

        $blogPost->update($validated);

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $blogPost)
    {
        if ($blogPost->featured_image) {
            Storage::disk('public')->delete($blogPost->featured_image);
        }
        if ($blogPost->og_image) {
            Storage::disk('public')->delete($blogPost->og_image);
        }
        $blogPost->delete();

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Blog post deleted successfully.');
    }

    public function toggleTrending(BlogPost $blogPost)
    {
        $blogPost->update(['is_trending' => !$blogPost->is_trending]);
        return redirect()->back()->with('success',
            $blogPost->is_trending ? 'Post marked as trending.' : 'Post removed from trending.');
    }

    public function toggleFeatured(BlogPost $blogPost)
    {
        $blogPost->update(['is_featured' => !$blogPost->is_featured]);
        return redirect()->back()->with('success',
            $blogPost->is_featured ? 'Post marked as featured.' : 'Post removed from featured.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:publish,draft,archive,delete,trending,notrending',
            'ids' => 'required|array',
            'ids.*' => 'exists:blog_posts,id',
        ]);

        $ids = $request->ids;
        $count = 0;

        switch ($request->action) {
            case 'publish':
                $count = BlogPost::whereIn('id', $ids)->update([
                    'status' => 'published',
                    'published_at' => now(),
                ]);
                break;
            case 'draft':
                $count = BlogPost::whereIn('id', $ids)->update(['status' => 'draft']);
                break;
            case 'archive':
                $count = BlogPost::whereIn('id', $ids)->update(['status' => 'archived']);
                break;
            case 'delete':
                $posts = BlogPost::whereIn('id', $ids)->get();
                foreach ($posts as $post) {
                    if ($post->featured_image) Storage::disk('public')->delete($post->featured_image);
                    if ($post->og_image) Storage::disk('public')->delete($post->og_image);
                    $post->delete();
                    $count++;
                }
                break;
            case 'trending':
                $count = BlogPost::whereIn('id', $ids)->update(['is_trending' => true]);
                break;
            case 'notrending':
                $count = BlogPost::whereIn('id', $ids)->update(['is_trending' => false]);
                break;
        }

        return redirect()->route('admin.blog.posts.index')
            ->with('success', "Bulk action completed. {$count} posts affected.");
    }
}
