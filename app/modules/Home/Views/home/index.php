<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 1rem;
        }

        .message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .features {
            text-align: left;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .features h3 {
            margin-top: 0;
            color: #495057;
        }

        .features ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .features li {
            margin-bottom: 0.5rem;
            color: #6c757d;
        }

        .api-link {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 1rem;
            transition: background 0.3s;
        }

        .api-link:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?= htmlspecialchars($title) ?></h1>
        <p class="message"><?= htmlspecialchars($message) ?></p>

        <div class="features">
            <h3>Fitur Utama:</h3>
            <ul>
                <?php foreach ($features as $feature): ?>
                    <li><?= htmlspecialchars($feature) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <a href="/api" class="api-link">Test API Endpoint</a>
    </div>
</body>

</html>
