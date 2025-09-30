<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post->title) ?> - Fusion Framework Blog</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .main-content {
            padding: 40px 0;
        }

        .post-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .post-image {
            width: 100%;
            height: 300px;
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .post-content {
            padding: 40px;
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .post-date {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .post-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-published {
            background: #d4edda;
            color: #155724;
        }

        .status-draft {
            background: #fff3cd;
            color: #856404;
        }

        .post-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .post-body {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #444;
        }

        .post-body h1,
        .post-body h2,
        .post-body h3,
        .post-body h4,
        .post-body h5,
        .post-body h6 {
            margin: 30px 0 15px 0;
            color: #2c3e50;
        }

        .post-body p {
            margin-bottom: 20px;
        }

        .post-body ul,
        .post-body ol {
            margin: 20px 0;
            padding-left: 30px;
        }

        .post-body li {
            margin-bottom: 8px;
        }

        .post-body blockquote {
            border-left: 4px solid #667eea;
            padding-left: 20px;
            margin: 20px 0;
            font-style: italic;
            color: #6c757d;
        }

        .post-body code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .post-body pre {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 20px 0;
        }

        .post-body pre code {
            background: none;
            padding: 0;
        }

        .related-posts {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .related-posts h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .related-list {
            list-style: none;
        }

        .related-list li {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .related-list li:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .related-list a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .related-list a:hover {
            color: #5a6fd8;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 30px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #5a6fd8;
        }

        .back-link::before {
            content: '←';
            margin-right: 8px;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a6fd8;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        @media (max-width: 768px) {
            .post-content {
                padding: 20px;
            }

            .post-title {
                font-size: 1.8rem;
            }

            .post-meta {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <h1>Blog Post</h1>
            <p>Read and discover amazing content</p>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <a href="/blog" class="back-link">Back to Blog</a>

            <article class="post-container">
                <div class="post-image">
                    <?php if (!empty($post->featured_image)): ?>
                        <img src="<?= htmlspecialchars($post->featured_image) ?>" alt="<?= htmlspecialchars($post->title) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span>📝</span>
                    <?php endif; ?>
                </div>

                <div class="post-content">
                    <div class="post-meta">
                        <span class="post-date">
                            Published on <?= date('F j, Y', strtotime($post->published_at)) ?>
                        </span>
                        <span class="post-status status-<?= $post->status ?>">
                            <?= ucfirst($post->status) ?>
                        </span>
                    </div>

                    <h1 class="post-title"><?= htmlspecialchars($post->title) ?></h1>

                    <div class="post-body">
                        <?= nl2br(htmlspecialchars($post->content)) ?>
                    </div>

                    <div class="actions">
                        <a href="/blog" class="btn btn-primary">← Back to Blog</a>
                        <a href="/blog/search" class="btn btn-secondary">Search Posts</a>
                    </div>
                </div>
            </article>

            <?php if (!empty($relatedPosts)): ?>
                <div class="related-posts">
                    <h3>Related Posts</h3>
                    <ul class="related-list">
                        <?php foreach ($relatedPosts as $relatedPost): ?>
                            <li>
                                <a href="/blog/<?= htmlspecialchars($relatedPost->slug) ?>">
                                    <?= htmlspecialchars($relatedPost->title) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
