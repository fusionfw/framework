<?php

namespace App\Modules\Blog\Services;

use Fusion\Core\Service;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Repositories\BlogRepository;

class BlogService extends Service
{
    protected $postRepository;

    public function __construct()
    {
        parent::__construct();
        $this->postRepository = new BlogRepository();
    }

    /**
     * Get published posts with pagination
     */
    public function getPublishedPosts($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;

        return Post::published()
            ->orderBy('published_at', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();
    }

    /**
     * Get count of published posts
     */
    public function getPublishedPostsCount()
    {
        return Post::published()->count();
    }

    /**
     * Get post by slug
     */
    public function getPostBySlug($slug)
    {
        return Post::published()->where('slug', $slug)->first();
    }

    /**
     * Get post by ID
     */
    public function getPost($id)
    {
        return Post::find($id);
    }

    /**
     * Get related posts
     */
    public function getRelatedPosts($postId, $limit = 3)
    {
        return Post::published()
            ->where('id', '!=', $postId)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search posts
     */
    public function searchPosts($query, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;

        return Post::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%")
                    ->orWhere('excerpt', 'LIKE', "%{$query}%");
            })
            ->orderBy('published_at', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();
    }

    /**
     * Get search results count
     */
    public function getSearchResultsCount($query)
    {
        return Post::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%")
                    ->orWhere('excerpt', 'LIKE', "%{$query}%");
            })
            ->count();
    }

    /**
     * Get posts by category
     */
    public function getPostsByCategory($category, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;

        return Post::published()
            ->where('category', $category)
            ->orderBy('published_at', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();
    }

    /**
     * Get posts by category count
     */
    public function getPostsByCategoryCount($category)
    {
        return Post::published()
            ->where('category', $category)
            ->count();
    }

    /**
     * Create a new post
     */
    public function createPost($data)
    {
        // Set default values
        $data['status'] = $data['status'] ?? 'draft';
        $data['author_id'] = $data['author_id'] ?? 1; // Default author
        $data['published_at'] = $data['status'] === 'published' ? now() : null;

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        // Generate excerpt if not provided
        if (empty($data['excerpt'])) {
            $data['excerpt'] = substr(strip_tags($data['content']), 0, 150) . '...';
        }

        return Post::create($data);
    }

    /**
     * Update a post
     */
    public function updatePost($id, $data)
    {
        $post = Post::find($id);

        if (!$post) {
            return false;
        }

        // Update published_at if status changed to published
        if (isset($data['status']) && $data['status'] === 'published' && $post->status !== 'published') {
            $data['published_at'] = now();
        }

        // Generate slug if title changed
        if (isset($data['title']) && $data['title'] !== $post->title) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        // Generate excerpt if content changed
        if (isset($data['content']) && $data['content'] !== $post->content) {
            $data['excerpt'] = substr(strip_tags($data['content']), 0, 150) . '...';
        }

        return $post->update($data);
    }

    /**
     * Delete a post
     */
    public function deletePost($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return false;
        }

        return $post->delete();
    }

    /**
     * Validate post data
     */
    public function validatePost($data, $id = null)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'in:draft,published',
        ];

        $messages = [
            'title.required' => 'Title is required',
            'title.max' => 'Title must not exceed 255 characters',
            'content.required' => 'Content is required',
            'status.in' => 'Status must be either draft or published',
        ];

        // Check for unique title if creating new post or title changed
        if (!$id || (isset($data['title']) && Post::where('title', $data['title'])->where('id', '!=', $id)->exists())) {
            $rules['title'] .= '|unique:posts,title' . ($id ? ",{$id}" : '');
            $messages['title.unique'] = 'A post with this title already exists';
        }

        $validation = $this->validate($data, $rules);

        return [
            'valid' => empty($validation),
            'errors' => $validation
        ];
    }

    /**
     * Generate unique slug
     */
    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');

        $originalSlug = $slug;
        $counter = 1;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get recent posts
     */
    public function getRecentPosts($limit = 5)
    {
        return Post::published()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get popular posts (by view count if implemented)
     */
    public function getPopularPosts($limit = 5)
    {
        return Post::published()
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get posts by author
     */
    public function getPostsByAuthor($authorId, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;

        return Post::published()
            ->where('author_id', $authorId)
            ->orderBy('published_at', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();
    }
}
