<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Fusion Framework</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .main-content {
            padding: 60px 0;
        }

        .search-bar {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            max-width: 500px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-btn {
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-btn:hover {
            background: #5a6fd8;
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .post-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .post-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .post-content {
            padding: 25px;
        }

        .post-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .post-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s;
        }

        .post-title a:hover {
            color: #667eea;
        }

        .post-excerpt {
            color: #6c757d;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .post-date {
            font-weight: 500;
        }

        .read-more {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .read-more:hover {
            color: #5a6fd8;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }

        .pagination a,
        .pagination span {
            padding: 10px 16px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            text-decoration: none;
            color: #667eea;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .no-posts {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-posts h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .stats {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            text-align: center;
        }

        .stats h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .stats p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .posts-grid {
                grid-template-columns: 1fr;
            }

            .search-form {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <h1>Blog</h1>
            <p>Welcome to our blog powered by Fusion Framework</p>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <!-- Search Bar -->
            <div class="search-bar">
                <form class="search-form" method="GET" action="/blog/search">
                    <input type="text" name="q" class="search-input" placeholder="Search posts..." value="<?= htmlspecialchars($query ?? '') ?>">
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>

            <!-- Stats -->
            <div class="stats">
                <h3>Blog Statistics</h3>
                <p>Total Posts: <?= $totalPosts ?? 0 ?></p>
            </div>

            <!-- Posts Grid -->
            <?php if (!empty($posts)): ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <div class="post-image">
                                <?php if (!empty($post->featured_image)): ?>
                                    <img src="<?= htmlspecialchars($post->featured_image) ?>" alt="<?= htmlspecialchars($post->title) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <span>📝</span>
                                <?php endif; ?>
                            </div>
                            <div class="post-content">
                                <h2 class="post-title">
                                    <a href="/blog/<?= htmlspecialchars($post->slug) ?>">
                                        <?= htmlspecialchars($post->title) ?>
                                    </a>
                                </h2>
                                <div class="post-excerpt">
                                    <?= htmlspecialchars($post->excerpt) ?>
                                </div>
                                <div class="post-meta">
                                    <span class="post-date">
                                        <?= date('M j, Y', strtotime($post->published_at)) ?>
                                    </span>
                                    <a href="/blog/<?= htmlspecialchars($post->slug) ?>" class="read-more">
                                        Read More →
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <div class="pagination">
                        <?php if (isset($currentPage) && $currentPage > 1): ?>
                            <a href="?page=<?= $currentPage - 1 ?>">← Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == ($currentPage ?? 1)): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if (isset($currentPage) && $currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage + 1 ?>">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-posts">
                    <h3>No posts found</h3>
                    <p>There are no blog posts available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
