<?php

namespace App\Modules\Blog\Controllers;

use Fusion\Core\Controller;
use Fusion\Core\Request;
use Fusion\Core\Response;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Services\BlogService;

class BlogController extends Controller
{
    protected $blogService;

    public function __construct()
    {
        parent::__construct();
        $this->blogService = new BlogService();
    }

    /**
     * Display a listing of posts
     */
    public function index(Request $request): Response
    {
        $page = $request->input('page', 1);
        $perPage = 10;

        $posts = $this->blogService->getPublishedPosts($page, $perPage);
        $totalPosts = $this->blogService->getPublishedPostsCount();

        $data = [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => ceil($totalPosts / $perPage),
            'totalPosts' => $totalPosts
        ];

        return $this->view('Blog.blog.index', $data);
    }

    /**
     * Display a single post
     */
    public function show(Request $request): Response
    {
        $slug = $request->input('slug');
        $post = $this->blogService->getPostBySlug($slug);

        if (!$post) {
            return $this->error('Post not found', 404);
        }

        $data = [
            'post' => $post,
            'relatedPosts' => $this->blogService->getRelatedPosts($post->id, 3)
        ];

        return $this->view('Blog.blog.show', $data);
    }

    /**
     * Show the form for creating a new post (Admin)
     */
    public function create(Request $request): Response
    {
        return $this->view('Blog.blog.create');
    }

    /**
     * Store a newly created post (Admin)
     */
    public function store(Request $request): Response
    {
        $data = $request->input();

        // Validate data
        $validation = $this->blogService->validatePost($data);
        if (!$validation['valid']) {
            return $this->error($validation['errors'], 422);
        }

        $post = $this->blogService->createPost($data);

        if ($post) {
            return $this->success($post, 'Post created successfully', 201);
        }

        return $this->error('Failed to create post', 500);
    }

    /**
     * Show the form for editing a post (Admin)
     */
    public function edit(Request $request): Response
    {
        $id = $request->input('id');
        $post = $this->blogService->getPost($id);

        if (!$post) {
            return $this->error('Post not found', 404);
        }

        $data = ['post' => $post];
        return $this->view('Blog.blog.edit', $data);
    }

    /**
     * Update the specified post (Admin)
     */
    public function update(Request $request): Response
    {
        $id = $request->input('id');
        $data = $request->input();

        // Validate data
        $validation = $this->blogService->validatePost($data, $id);
        if (!$validation['valid']) {
            return $this->error($validation['errors'], 422);
        }

        $post = $this->blogService->updatePost($id, $data);

        if ($post) {
            return $this->success($post, 'Post updated successfully');
        }

        return $this->error('Failed to update post', 500);
    }

    /**
     * Delete the specified post (Admin)
     */
    public function destroy(Request $request): Response
    {
        $id = $request->input('id');

        if ($this->blogService->deletePost($id)) {
            return $this->success(null, 'Post deleted successfully');
        }

        return $this->error('Failed to delete post', 500);
    }

    /**
     * Search posts
     */
    public function search(Request $request): Response
    {
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;

        $posts = $this->blogService->searchPosts($query, $page, $perPage);
        $totalPosts = $this->blogService->getSearchResultsCount($query);

        $data = [
            'posts' => $posts,
            'query' => $query,
            'currentPage' => $page,
            'totalPages' => ceil($totalPosts / $perPage),
            'totalPosts' => $totalPosts
        ];

        return $this->view('Blog.blog.search', $data);
    }

    /**
     * Get posts by category (if categories are implemented)
     */
    public function category(Request $request): Response
    {
        $category = $request->input('category');
        $page = $request->input('page', 1);
        $perPage = 10;

        $posts = $this->blogService->getPostsByCategory($category, $page, $perPage);
        $totalPosts = $this->blogService->getPostsByCategoryCount($category);

        $data = [
            'posts' => $posts,
            'category' => $category,
            'currentPage' => $page,
            'totalPages' => ceil($totalPosts / $perPage),
            'totalPosts' => $totalPosts
        ];

        return $this->view('Blog.blog.category', $data);
    }
}
