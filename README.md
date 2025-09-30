# Fusion Framework

**Fusion Framework** adalah framework PHP mandiri dengan dua mode runtime: Lite dan Enterprise. Versi ini telah direfaktor dari gabungan Flexify/Flight menjadi framework independen yang berdiri sendiri.

## 🚀 Tentang Fusion Framework

Fusion mendukung dua mode runtime yang dapat dipilih melalui `.env`:

- **Lite Mode (APP_MODE=lite)**: Clean MVC + Service + Repository, middleware & konfigurasi minimal, CLI sederhana. Cocok untuk pemula dan aplikasi kecil.
- **Enterprise Mode (APP_MODE=enterprise)**: Fitur lengkap seperti Plugin System, AuthManager, Multi-driver Cache, Benchmark, Starter Kits, CLI advanced. Cocok untuk aplikasi enterprise/SaaS.

Contoh konfigurasi `.env` dasar:

```
APP_NAME=FusionFramework
APP_MODE=lite   # lite | enterprise
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
```

Fusion Framework adalah framework PHP yang powerful dengan:

- **Dual-Mode Architecture** - Lite mode untuk pemula, Enterprise mode untuk aplikasi besar
- **Clean MVC Pattern** - Controller, Model, Service, Repository yang mudah dipahami
- **Plugin System** - Arsitektur yang dapat diperluas (Enterprise mode)
- **Multi-Driver Support** - Cache, Session, Database dengan multiple drivers
- **Comprehensive CLI** - Command line tools yang adaptif sesuai mode
- **Production Ready** - Siap untuk development hingga production

## 🎯 Fitur Utama

### **Fusion Lite Mode:**

- **Clean MVC** - Controller, Model, View yang sederhana
- **Service Layer** - Enkapsulasi business logic
- **Repository Pattern** - Abstraksi data access
- **Basic CLI** - Command line tools essential
- **Minimal Dependencies** - Ringan dan cepat
- **Perfect for Learning** - Ideal untuk pemula

### **Fusion Enterprise Mode:**

- **Plugin System** - Arsitektur yang dapat diperluas
- **Benchmark Tools** - Built-in performance measurement
- **Multi-driver Support** - Cache, Session, Database drivers
- **Advanced CLI** - Command line tools comprehensive
- **Auth System** - Complete authentication & authorization
- **Production Ready** - Fitur lengkap untuk aplikasi besar

### **Shared Features:**

- **Dual-Mode Runtime** - Switch mode via `.env`
- **Modern Architecture** - PSR-4 autoloading, DI Container
- **Clean Code** - Kode yang bersih dan mudah dipahami
- **Fast Performance** - Optimized untuk kecepatan
- **Easy Learning** - Learning curve yang rendah

## 📊 Perbandingan Mode

| Fitur                  | Lite Mode              | Enterprise Mode                  |
| ---------------------- | ---------------------- | -------------------------------- |
| **Target User**        | Pemula, Aplikasi Kecil | Enterprise, SaaS, Aplikasi Besar |
| **MVC Pattern**        | ✅ Basic               | ✅ Advanced                      |
| **Service Layer**      | ✅                     | ✅                               |
| **Repository Pattern** | ✅                     | ✅                               |
| **Plugin System**      | ❌                     | ✅                               |
| **Auth System**        | ❌                     | ✅                               |
| **Cache System**       | ❌                     | ✅                               |
| **Session Management** | ❌                     | ✅                               |
| **Benchmark Tools**    | ❌                     | ✅                               |
| **CLI Commands**       | Basic (15 commands)    | Full (25+ commands)              |
| **Memory Usage**       | ~2MB                   | ~5MB                             |
| **Startup Time**       | ~50ms                  | ~100ms                           |
| **Dependencies**       | Minimal                | Full                             |

## 🚀 Quick Start

### **Installation**

1. **Clone atau Download Framework**

```bash
git clone https://github.com/your-repo/fusion-framework.git
cd fusion-framework
```

2. **Install Dependencies**

```bash
composer install
```

3. **Environment Setup**

```bash
cp env.example .env
# Edit .env and set your preferred mode
# APP_MODE=lite        # For beginners/small apps
# APP_MODE=enterprise  # For advanced/enterprise apps
php fusion key:generate
```

4. **Database Setup** (Optional)

```bash
php fusion migrate
php fusion db:seed
```

5. **Start Development Server**

```bash
# Lite mode (default)
php fusion serve

# Or force enterprise mode
php fusion enterprise serve
```

6. **Access Application**

```
http://localhost:8000
```

### **Mode Selection**

Fusion Framework mendukung dua mode yang dapat dipilih:

**Lite Mode (Default):**

```bash
# Set di .env
APP_MODE=lite

# Atau paksa via CLI
php fusion lite <command>
```

**Enterprise Mode:**

```bash
# Set di .env
APP_MODE=enterprise

# Atau paksa via CLI
php fusion enterprise <command>
```

**Mode Switching:**

```bash
# Switch mode di .env
echo "APP_MODE=enterprise" >> .env

# Atau paksa mode untuk command tertentu
php fusion enterprise serve
php fusion lite make:controller TestController
```

### **Available Commands**

**Lite Mode Commands:**

```bash
php fusion help                    # Show help
php fusion serve                   # Start server
php fusion make:controller <name>  # Create controller
php fusion make:model <name>       # Create model
php fusion make:service <name>     # Create service
php fusion make:repository <name>  # Create repository
php fusion make:module <name>      # Create module
php fusion migrate                 # Run migrations
php fusion db:seed                 # Run seeders
```

**Enterprise Mode Commands:**

```bash
# Semua Lite commands +
php fusion plugin:list             # List plugins
php fusion plugin:install <name>   # Install plugin
php fusion benchmark               # Run benchmarks
php fusion optimize                # Optimize app
php fusion cache:clear             # Clear cache
php fusion config:cache            # Cache config
```

### **Create Your First Module**

```bash
# Create a new module
php fusion make:module Blog

# Create controller
php fusion make:controller PostController Blog

# Create model
php fusion make:model Post Blog

# Create service
php fusion make:service PostService Blog

# Create repository
php fusion make:repository PostRepository Blog
```

### **Mode Usage Examples**

**Lite Mode - Simple Blog:**

```php
// app/modules/Blog/Controllers/PostController.php
<?php
namespace App\Modules\Blog\Controllers;

use Fusion\Core\Controller;

class PostController extends Controller
{
    public function index()
    {
        $posts = $this->service('PostService')->all();
        return $this->view('blog.index', compact('posts'));
    }
}
```

**Enterprise Mode - Advanced Features:**

```php
// app/modules/Blog/Controllers/PostController.php
<?php
namespace App\Modules\Blog\Controllers;

use Fusion\Core\Controller;

class PostController extends Controller
{
    public function index()
    {
        // Use cache in enterprise mode
        $posts = $this->cache->remember('posts', 3600, function() {
            return $this->service('PostService')->all();
        });

        return $this->view('blog.index', compact('posts'));
    }
}
```

**Mode Detection in Code:**

```php
// Check current mode
$app = \Fusion\Core\Application::getInstance();
$mode = $app->getMode();

if ($mode === 'enterprise') {
    // Use enterprise features
    $this->cache->put('key', 'value');
    $this->auth->check();
} else {
    // Use lite features
    $this->service('UserService')->all();
}
```

### **Migration Guide**

**From Lite to Enterprise:**

```bash
# 1. Update .env
echo "APP_MODE=enterprise" >> .env

# 2. Install additional dependencies (if needed)
composer install

# 3. Run enterprise commands
php fusion enterprise optimize
php fusion enterprise benchmark
```

**From Enterprise to Lite:**

```bash
# 1. Update .env
echo "APP_MODE=lite" >> .env

# 2. Remove enterprise-specific code
# - Remove cache usage
# - Remove auth checks
# - Remove plugin calls
```

### **Performance Comparison**

**Lite Mode Performance:**

- Memory Usage: ~2MB
- Startup Time: ~50ms
- Commands: 15 basic commands
- Dependencies: Minimal
- Best for: Learning, prototyping, small apps

**Enterprise Mode Performance:**

- Memory Usage: ~5MB
- Startup Time: ~100ms
- Commands: 25+ advanced commands
- Dependencies: Full
- Best for: Production, enterprise, large apps

### **Best Practices**

**Choose Lite Mode When:**

- Learning PHP frameworks
- Building simple websites
- Prototyping applications
- Working with limited resources
- Need fast development

**Choose Enterprise Mode When:**

- Building production applications
- Need advanced features (cache, auth, plugins)
- Working with large teams
- Building SaaS applications
- Need performance optimization

### **Troubleshooting**

**Mode Not Switching:**

```bash
# Clear application cache
php fusion cache:clear

# Reset application instance
php fusion enterprise help
```

**Command Not Available:**

```bash
# Check current mode
php fusion help

# Force enterprise mode
php fusion enterprise <command>
```

**Performance Issues:**

```bash
# Optimize for production
php fusion enterprise optimize

# Run benchmarks
php fusion enterprise benchmark
```

### **Basic Usage**

**Controller Example:**

```php
<?php
namespace App\Modules\Blog\Controllers;

use Fusion\Core\Controller;
use Fusion\Core\Request;
use Fusion\Core\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = $this->service('PostService')->getAllPosts();
        return $this->view('Blog.post.index', ['posts' => $posts]);
    }

    public function show(Request $request): Response
    {
        $id = $request->input('id');
        $post = $this->service('PostService')->getPost($id);
        return $this->json($post);
    }
}
```

**Model Example:**

```php
<?php
namespace App\Modules\Blog\Models;

use Fusion\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'status'];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
```

**Service Example:**

```php
<?php
namespace App\Modules\Blog\Services;

use Fusion\Core\Service;

class PostService extends Service
{
    public function getAllPosts()
    {
        return $this->repository('PostRepository')->findAll();
    }

    public function createPost($data)
    {
        $validation = $this->validate($data, [
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        if (empty($validation)) {
            return $this->repository('PostRepository')->create($data);
        }

        return false;
    }
}
```

## 📁 Struktur Folder

```
fusion/
├── app/
│   ├── modules/
│   │   └── {Module}/
│   │       ├── Controllers/
│   │       ├── Models/
│   │       ├── Services/
│   │       ├── Repositories/
│   │       ├── Views/
│   │       └── routes.php
│   └── Middleware/
├── core/
│   ├── Application.php
│   ├── Autoloader.php
│   ├── Container.php
│   ├── Router.php
│   ├── Request.php
│   ├── Response.php
│   ├── Controller.php          ← Enhanced dengan Flight features
│   ├── Model.php              ← Enhanced dengan Flight features
│   ├── Service.php            ← Enhanced dengan Flight features
│   ├── Repository.php         ← Enhanced dengan Flight features
│   ├── Middleware.php
│   ├── Security.php
│   ├── Config.php
│   ├── Logger.php
│   ├── Console.php            ← Enhanced dengan HMF compatibility
│   ├── Database/
│   │   ├── Connection.php
│   │   ├── QueryBuilder.php
│   │   ├── Migration.php
│   │   └── Migrator.php
│   ├── Session/
│   │   └── SessionManager.php
│   ├── Cache/
│   │   ├── CacheManager.php
│   │   ├── CacheInterface.php
│   │   ├── FileCache.php
│   │   └── ArrayCache.php
│   ├── Auth/
│   │   ├── AuthManager.php
│   │   └── UserProvider.php
│   ├── Plugin/
│   │   ├── PluginInterface.php
│   │   └── PluginManager.php
│   └── Benchmark/
│       └── BenchmarkRunner.php
├── config/
│   ├── app.php
│   └── database.php
├── plugins/
│   ├── Payment/
│   │   └── Payment.php
│   └── Queue/
│       └── Queue.php
├── storage/
│   ├── logs/
│   └── cache/
├── database/
│   └── migrations/
├── public/
│   ├── index.php
│   └── .htaccess
├── tests/
├── vendor/
├── flexify                   ← CLI tool dengan HMF compatibility
├── bootstrap.php
├── composer.json
├── phpunit.xml
├── .gitignore
└── README.md
```

## 🛠️ Instalasi

### 1. Clone Repository

```bash
git clone <repository-url> fusion-framework
cd fusion-framework
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Setup Environment

```bash
cp .env.example .env
# Edit file .env sesuai konfigurasi Anda
```

### 4. Set Permissions

```bash
chmod +x flexify
chmod -R 755 storage/
```

### 5. Start Development Server

```bash
# Menggunakan CLI tool
./fusion serve

# Atau menggunakan PHP built-in server
php -S localhost:8000 -t public
```

## 📖 Penggunaan

### 1. Membuat Module Baru

```bash
# Menggunakan Fusion CLI (recommended)
./fusion make:module Blog

# Legacy commands (deprecated, akan dihapus di versi 2.0)
./fusion make:module Blog
./flexify hmf make:module Blog
```

### 2. Membuat Komponen

```bash
# Controller
./fusion make:controller PostController Blog

# Model
./fusion make:model Post Blog

# Service
./fusion make:service PostService Blog

# Repository
./fusion make:repository PostRepository Blog

# Middleware
./fusion make:middleware AuthMiddleware

# Legacy commands (deprecated)
./fusion make:controller PostController Blog
./flexify hmf make:controller PostController Blog
```

### 3. Routing

```php
// app/modules/Blog/routes.php
use Fusion\Core\Router;

$router = new Router();

// Basic routes
$router->get('/posts', 'Blog\Controllers\PostController@index');
$router->post('/posts', 'Blog\Controllers\PostController@store');
$router->get('/posts/{id}', 'Blog\Controllers\PostController@show');

// Route groups
$router->group(['prefix' => '/api', 'middleware' => ['AuthMiddleware']], function($router) {
    $router->get('/posts', 'Blog\Controllers\PostController@index');
    $router->post('/posts', 'Blog\Controllers\PostController@store');
});
```

### 4. Controller (Fusion Style)

```php
<?php
namespace App\Modules\Blog\Controllers;

use Fusion\Core\Controller;
use Fusion\Core\Request;
use Fusion\Core\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = $this->service('PostService')->getAllPosts();
        return $this->view('Blog.post.index', ['posts' => $posts]);
    }

    public function store(Request $request): Response
    {
        $data = $request->input();
        $post = $this->service('PostService')->createPost($data);
        return $this->success($post, 'Post created successfully');
    }

    // Flight style compatibility
    public function renderView(string $view, array $data = []): void
    {
        $this->render($view, $data);
    }
}
```

### 5. Model (Fusion Style)

```php
<?php
namespace App\Modules\Blog\Models;

use Fusion\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'slug', 'published'];

    // Flexify style methods
    public static function all(): array
    {
        return parent::all();
    }

    // Flight style compatibility
    public static function findAll(): array
    {
        return static::all();
    }

    public static function findById($id)
    {
        return static::find($id);
    }

    public static function findBy(string $column, $value)
    {
        return static::firstWhere($column, '=', $value);
    }
}
```

### 6. Service (Fusion Style)

```php
<?php
namespace App\Modules\Blog\Services;

use Fusion\Core\Service;

class PostService extends Service
{
    public function getAllPosts(): array
    {
        return $this->repository('PostRepository')->all();
    }

    public function createPost(array $data)
    {
        // Flight style validation
        $errors = $this->validate($data, [
            'title' => 'required|min:3|max:255',
            'content' => 'required|min:10'
        ]);

        if (!empty($errors)) {
            throw new \Exception('Validation failed: ' . implode(', ', $errors));
        }

        // Sanitize data
        $data = $this->sanitize($data);

        return $this->repository('PostRepository')->create($data);
    }
}
```

### 7. Repository (Fusion Style)

```php
<?php
namespace App\Modules\Blog\Repositories;

use Fusion\Core\Repository;

class PostRepository extends Repository
{
    protected $table = 'posts';
    protected $primaryKey = 'id';

    // Flexify style methods
    public function all(): array
    {
        return parent::all();
    }

    // Flight style compatibility
    public function findAll(): array
    {
        return $this->all();
    }

    public function findById($id): ?array
    {
        return $this->find($id);
    }

    public function findBy(string $column, $value): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
```

## 🔧 CLI Commands

### **Fusion Commands (Recommended)**

```bash
# Development
./fusion serve [host] [port]
./fusion migrate
./fusion migrate:rollback
./fusion migrate:reset
./fusion migrate:status

# Code Generation
./fusion make:controller <ControllerName> [Module]
./fusion make:model <ModelName> [Module]
./fusion make:service <ServiceName> [Module]
./fusion make:repository <RepositoryName> [Module]
./fusion make:middleware <MiddlewareName>
./fusion make:module <ModuleName>

# Project Management
./fusion new <template> <project-name>
./fusion benchmark [url] [concurrency] [requests]

# Plugin Management
./fusion plugin:list
./fusion plugin:install <plugin-name>
./fusion plugin:uninstall <plugin-name>
./fusion plugin:activate <plugin-name>
./fusion plugin:deactivate <plugin-name>
```

### **Legacy Commands (Deprecated)**

```bash
# ⚠️ WARNING: Commands ini akan dihapus di versi 2.0
# Gunakan ./fusion sebagai gantinya

# Legacy Flexify commands
./fusion serve [host] [port]
./fusion migrate
./fusion make:controller <ControllerName> [Module]
./fusion make:model <ModelName> [Module]
./fusion make:service <ServiceName> [Module]
./fusion make:repository <RepositoryName> [Module]
./fusion make:middleware <MiddlewareName>
./fusion make:module <ModuleName>

# Legacy HMF commands
./flexify hmf serve [host] [port]
./flexify hmf migrate
./flexify hmf make:controller <ControllerName> [Module]
./flexify hmf make:model <ModelName> [Module]
./flexify hmf make:service <ServiceName> [Module]
./flexify hmf make:repository <RepositoryName> [Module]
./flexify hmf make:middleware <MiddlewareName>
./flexify hmf make:module <ModuleName>
```

## 🔒 Security Features

- **CSRF Protection** - Automatic CSRF token generation and validation
- **XSS Protection** - Input sanitization and output escaping
- **Password Hashing** - Secure password hashing using PHP's password_hash()
- **Rate Limiting** - Built-in rate limiting functionality
- **Security Headers** - Automatic security headers in responses
- **Session Security** - Secure session management with configurable options
- **Input Validation** - Built-in validation with custom rules

## 📝 Logging

```php
// Menggunakan logger
$this->logger()->info('User logged in', ['user_id' => $userId]);
$this->logger()->error('Database error', ['error' => $errorMessage]);

// Flight style logging
$this->log('User action performed', 'info');
```

## 🗄️ Database & Migrations

```php
// Query Builder
$users = User::query()
    ->where('active', true)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

### Database Configuration

Fusion Framework mendukung multiple database drivers:

```bash
# SQLite (Default untuk development)
DB_CONNECTION=sqlite
DB_DATABASE=database.sqlite

# MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fusion_framework
DB_USERNAME=root
DB_PASSWORD=
```

### Migration Commands

| Command                       | Deskripsi                                       |
| ----------------------------- | ----------------------------------------------- |
| `php fusion migrate`          | Menjalankan semua migration yang pending        |
| `php fusion migrate:status`   | Menampilkan status migration dalam bentuk tabel |
| `php fusion migrate:rollback` | Rollback migration terakhir                     |
| `php fusion migrate:reset`    | Reset semua migration                           |

### Contoh Output migrate:status

```
Migration Status:
================
=====================================================
Migration                         Status     Batch
=====================================================
20231201120000_create_users_table ✓ Ran    1
20231201130000_create_posts_table ✗ Pending -
=====================================================
```

## 🚀 Queue System

Fusion Framework menyediakan sistem queue yang powerful dengan multiple drivers untuk menangani background jobs.

### Queue Configuration

```bash
# .env configuration
QUEUE_DRIVER=file          # sync | file | redis
QUEUE_PATH=storage/queue   # Path untuk file driver
QUEUE_RETRY_AFTER=90       # Retry delay in seconds
QUEUE_MAX_TRIES=3          # Maximum retry attempts
QUEUE_TIMEOUT=60           # Job timeout in seconds
```

### Queue Drivers

| Driver      | Deskripsi              | Use Case                    | Dependencies              |
| ----------- | ---------------------- | --------------------------- | ------------------------- |
| `sync`      | Eksekusi langsung      | Development, testing        | -                         |
| `file`      | Simpan ke file JSON    | Production tanpa Redis      | -                         |
| `redis`     | Menggunakan Redis      | High-performance production | `predis/predis`           |
| `beanstalk` | Menggunakan Beanstalk  | Simple queue system         | `pheanstalk/pheanstalk`   |
| `rabbitmq`  | Menggunakan RabbitMQ   | Enterprise messaging        | `php-amqplib/php-amqplib` |
| `sqs`       | Menggunakan Amazon SQS | Cloud-based queue           | `aws/aws-sdk-php`         |

### Queue Commands

| Command                                            | Deskripsi                      |
| -------------------------------------------------- | ------------------------------ |
| `php fusion queue:push JobClass [--driver=driver]` | Push job ke queue              |
| `php fusion queue:work [--driver=driver]`          | Jalankan worker (consume jobs) |
| `php fusion queue:failed [--driver=driver]`        | Tampilkan failed jobs          |
| `php fusion queue:retry JobID [--driver=driver]`   | Retry failed job               |
| `php fusion queue:clear [--driver=driver]`         | Clear semua jobs               |
| `php fusion queue:drivers`                         | Tampilkan driver yang tersedia |

### Membuat Job

```php
<?php
// app/Jobs/SendEmailJob.php
namespace App\Jobs;

use Fusion\Core\Queue\Job;

class SendEmailJob extends Job
{
    public function handle(): void
    {
        $email = $this->data['email'] ?? 'user@example.com';
        $subject = $this->data['subject'] ?? 'Welcome!';

        echo "Sending email to {$email}\n";
        echo "Subject: {$subject}\n";

        // Your email sending logic here
    }
}
```

### Menggunakan Queue

```php
// Push job ke queue
$queue = $this->queue();
$queue->push(SendEmailJob::class, [
    'email' => 'user@example.com',
    'subject' => 'Welcome!'
]);

// Push dengan delay
$queue->push(SendEmailJob::class, $data, 60); // 60 seconds delay
```

### Setup Driver

#### Redis Driver

```bash
# Install Predis
composer require predis/predis

# Konfigurasi .env
QUEUE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DATABASE=0
REDIS_PASSWORD=
QUEUE_REDIS_QUEUE=fusion_jobs
```

#### Beanstalk Driver

```bash
# Install Pheanstalk
composer require pheanstalk/pheanstalk

# Install Beanstalk server
# Ubuntu/Debian: sudo apt-get install beanstalkd
# macOS: brew install beanstalkd

# Konfigurasi .env
QUEUE_DRIVER=beanstalk
BEANSTALK_HOST=127.0.0.1
BEANSTALK_PORT=11300
BEANSTALK_QUEUE=fusion_jobs
```

#### RabbitMQ Driver

```bash
# Install PhpAmqpLib
composer require php-amqplib/php-amqplib

# Install RabbitMQ server
# Ubuntu/Debian: sudo apt-get install rabbitmq-server
# macOS: brew install rabbitmq

# Konfigurasi .env
QUEUE_DRIVER=rabbitmq
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASS=guest
RABBITMQ_QUEUE=fusion_jobs
```

#### Amazon SQS Driver

```bash
# Install AWS SDK
composer require aws/aws-sdk-php

# Konfigurasi .env
QUEUE_DRIVER=sqs
AWS_KEY=your-aws-access-key
AWS_SECRET=your-aws-secret-key
AWS_REGION=ap-southeast-1
SQS_QUEUE_URL=https://sqs.ap-southeast-1.amazonaws.com/123456789012/your-queue
SQS_FAILED_QUEUE_URL=https://sqs.ap-southeast-1.amazonaws.com/123456789012/your-queue-failed
```

### Contoh Penggunaan

```bash
# Lihat driver yang tersedia
php fusion queue:drivers

# Push job dengan driver tertentu
php fusion queue:push "App\Jobs\SendEmailJob" '{"email":"user@example.com"}' --driver=redis

# Jalankan worker dengan driver tertentu
php fusion queue:work --driver=file

# Lihat failed jobs dengan driver tertentu
php fusion queue:failed --driver=redis

# Retry failed job dengan driver tertentu
php fusion queue:retry job_1234567890 --driver=file

# Clear queue dengan driver tertentu
php fusion queue:clear --driver=redis
```

## 🔐 Authentication

```php
// Login user
$auth = $this->auth();
$auth->attempt(['email' => $email, 'password' => $password]);

// Check authentication
if ($auth->check()) {
    $user = $auth->user();
}

// Logout
$auth->logout();
```

## 💾 Session & Cache

```php
// Session
$this->session()->set('key', 'value');
$value = $this->session()->get('key');

// Cache
$this->cache()->set('key', 'value', 3600);
$value = $this->cache()->get('key');
```

## 🔌 Plugin System

```php
// Plugin management
./fusion plugin:list
./fusion plugin:install Payment
./fusion plugin:activate Payment

// Create custom plugin
class MyPlugin implements PluginInterface {
    public function getName(): string {
        return 'MyPlugin';
    }
    // ... implement other methods
}
```

## 📊 Benchmarking

```bash
# Run performance benchmarks
./fusion benchmark

# HTTP load testing
./fusion benchmark http://localhost:8000 10 100
```

## ⚙️ Konfigurasi

Framework menggunakan file konfigurasi di folder `config/` dan environment variables dari file `.env`.

```php
// Mengakses config
$appName = $this->config()->get('app.name');
$dbHost = $this->config()->get('database.connections.mysql.host');
```

## 🧪 Testing

```bash
# Run tests
composer test
# atau
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/ExampleTest.php
```

## 🚀 Performance

Fusion Framework dirancang untuk performa tinggi:

- **Lightweight Core** - Minimal overhead
- **Query Builder** - Optimized database queries
- **Caching System** - Built-in caching untuk performa
- **Memory Efficient** - Optimized memory usage
- **Benchmark Tools** - Built-in performance measurement

### Benchmark Results (Sample)

```
Test: Basic Routing
RPS: 15,000+
Avg Latency: 0.067ms

Test: Model Operations
RPS: 8,000+
Avg Latency: 0.125ms

Test: Cache Operations
RPS: 25,000+
Avg Latency: 0.040ms
```

## 🔧 Advanced Usage

### **CLI Commands**

Fusion Framework menyediakan CLI yang comprehensive:

```bash
# Development
php fusion serve [host] [port]     # Start development server
php fusion tinker                   # Interactive shell

# Database
php fusion migrate                  # Run migrations
php fusion migrate:rollback         # Rollback last migration
php fusion migrate:reset            # Reset all migrations
php fusion migrate:status           # Show migration status
php fusion db:seed                  # Run database seeders

# Code Generation
php fusion make:controller <name> [module]  # Create controller
php fusion make:model <name> [module]       # Create model
php fusion make:service <name> [module]     # Create service
php fusion make:repository <name> [module]  # Create repository
php fusion make:middleware <name>            # Create middleware
php fusion make:module <name>                # Create module
php fusion make:seeder <name>                # Create seeder
php fusion make:factory <name>               # Create factory
php fusion make:request <name>               # Create form request
php fusion make:job <name>                   # Create job

# Configuration
php fusion key:generate             # Generate application key
php fusion config:cache             # Cache configuration
php fusion cache:clear              # Clear application cache
php fusion optimize                 # Optimize application

# Routing
php fusion route:list               # List all routes

# Queue
php fusion queue:work               # Start queue worker
php fusion queue:restart            # Restart queue workers

# Storage
php fusion storage:link             # Create storage link

# Plugin Management
php fusion plugin:list              # List installed plugins
php fusion plugin:install <name>    # Install plugin
php fusion plugin:uninstall <name>  # Uninstall plugin
php fusion plugin:activate <name>   # Activate plugin
php fusion plugin:deactivate <name> # Deactivate plugin

# Performance
php fusion benchmark [url]          # Run performance benchmarks

# Project Management
php fusion new <template> <name>    # Create new project
```

### **Plugin System**

Fusion Framework memiliki sistem plugin yang powerful:

```php
<?php
// plugins/MyPlugin/MyPlugin.php
namespace Plugins\MyPlugin;

use Fusion\Core\Plugin\PluginInterface;

class MyPlugin implements PluginInterface
{
    public function boot()
    {
        // Plugin initialization
    }

    public function register()
    {
        // Register services, routes, etc.
    }

    public function activate()
    {
        // Plugin activation logic
    }

    public function deactivate()
    {
        // Plugin deactivation logic
    }
}
```

### **Middleware System**

```php
<?php
// app/Middleware/AuthMiddleware.php
namespace App\Middleware;

use Fusion\Core\Middleware;
use Fusion\Core\Request;
use Fusion\Core\Response;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request): ?Response
    {
        if (!$this->isAuthenticated($request)) {
            return $this->redirect('/login');
        }

        return null; // Continue to next middleware/controller
    }

    private function isAuthenticated(Request $request): bool
    {
        // Your authentication logic
        return isset($_SESSION['user_id']);
    }
}
```

### **Database Migrations**

```php
<?php
// database/migrations/20231201120000_create_users_table.php
use Fusion\Core\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->createTable('users', function($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();

            $table->index('email');
        });
    }

    public function down()
    {
        $this->dropTable('users');
    }
}
```

### **Database Seeders**

```php
<?php
// database/seeders/UserSeeder.php
class UserSeeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT)
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT)
            ]
        ];

        foreach ($users as $user) {
            // Insert user logic
        }
    }
}
```

### **Caching System**

```php
<?php
// Using cache in your application
use Fusion\Core\Cache\CacheManager;

$cache = CacheManager::getInstance();

// Store data
$cache->set('key', 'value', 3600); // 1 hour

// Retrieve data
$value = $cache->get('key');

// Check if exists
if ($cache->has('key')) {
    // Do something
}

// Remove data
$cache->forget('key');

// Clear all cache
$cache->flush();
```

### **Authentication System**

```php
<?php
// Using authentication
use Fusion\Core\Auth\AuthManager;

$auth = AuthManager::getInstance();

// Login user
if ($auth->attempt($email, $password)) {
    // User logged in successfully
}

// Check if user is authenticated
if ($auth->check()) {
    $user = $auth->user();
}

// Logout user
$auth->logout();

// Get current user
$user = $auth->user();
```

### **Performance Optimization**

```bash
# Optimize application
php fusion optimize

# Clear cache
php fusion cache:clear

# Cache configuration
php fusion config:cache

# Run benchmarks
php fusion benchmark

# Run specific benchmark
php fusion benchmark http://localhost:8000 10 100
```

## 🎯 Perbandingan dengan Framework Lain

| Fitur               | Fusion     | Laravel    | CodeIgniter 4 | Symfony    | Flight (HMF) | Flexify    |
| ------------------- | ---------- | ---------- | ------------- | ---------- | ------------ | ---------- |
| **Learning Curve**  | ⭐⭐⭐⭐⭐ | ⭐⭐       | ⭐⭐⭐⭐      | ⭐⭐       | ⭐⭐⭐⭐⭐   | ⭐⭐⭐     |
| **Performance**     | ⭐⭐⭐⭐⭐ | ⭐⭐⭐     | ⭐⭐⭐⭐      | ⭐⭐⭐     | ⭐⭐⭐⭐     | ⭐⭐⭐⭐⭐ |
| **Security**        | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐   | ⭐⭐⭐        | ⭐⭐⭐⭐   | ⭐⭐⭐⭐     | ⭐⭐⭐⭐⭐ |
| **Modularity**      | ⭐⭐⭐⭐⭐ | ⭐⭐⭐     | ⭐⭐⭐        | ⭐⭐⭐⭐⭐ | ⭐⭐⭐       | ⭐⭐⭐⭐⭐ |
| **CLI Tools**       | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐        | ⭐⭐⭐⭐   | ⭐⭐⭐       | ⭐⭐⭐⭐⭐ |
| **Plugin System**   | ⭐⭐⭐⭐⭐ | ⭐⭐⭐     | ⭐⭐          | ⭐⭐⭐⭐   | ❌           | ⭐⭐⭐⭐⭐ |
| **Benchmarking**    | ⭐⭐⭐⭐⭐ | ⭐⭐       | ⭐⭐          | ⭐⭐       | ❌           | ⭐⭐⭐⭐⭐ |
| **Starter Kits**    | ⭐⭐⭐⭐⭐ | ⭐⭐⭐     | ⭐⭐          | ⭐⭐       | ❌           | ⭐⭐⭐⭐⭐ |
| **Backward Compat** | ⭐⭐⭐⭐⭐ | ❌         | ❌            | ❌         | ⭐⭐⭐⭐⭐   | ❌         |

## 🏆 Keunggulan Fusion Framework

- **Best of Both Worlds** - Menggabungkan keunggulan Flexify dan Flight
- **Enterprise-Grade** - Siap untuk production dengan fitur lengkap
- **Developer-Friendly** - Mudah dipelajari dan digunakan
- **High Performance** - Optimized untuk kecepatan
- **Modular Architecture** - Scalable dan maintainable
- **Plugin Ecosystem** - Extensible dengan plugin system
- **Backward Compatibility** - Mendukung kode dari kedua framework asli
- **Dual CLI Support** - Fleksibilitas dalam penggunaan CLI
- **Clean Code** - Kode yang bersih dan mudah dipahami
- **Indonesian-First** - Dibuat untuk developer Indonesia

## 📄 Lisensi

Framework ini menggunakan lisensi MIT. Lihat file [LICENSE](LICENSE) untuk detail lebih lanjut.

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan buat issue atau pull request untuk perbaikan dan fitur baru.

## 📞 Support

Jika Anda memiliki pertanyaan atau butuh bantuan, silakan buat issue di repository ini.

## 🎉 Getting Started

1. **Install the framework**
2. **Run `./fusion migrate`** to set up the database
3. **Start the server** with `./fusion serve`
4. **Create your first module** with `./fusion make:module YourModule`
5. **Try legacy compatibility** with `./flexify make:controller TestController` (deprecated)

## ⚠️ Important Notice - CLI Changes

**Fusion Framework** sekarang menggunakan CLI `fusion` sebagai command utama.

### ✅ **Recommended (New)**

```bash
./fusion serve
./fusion make:module Blog
./fusion make:controller PostController Blog
```

### ⚠️ **Legacy (Deprecated)**

```bash
# Masih berfungsi tapi akan menampilkan warning
./flexify serve                    # ⚠️ Deprecated, gunakan ./fusion
./flexify hmf make:module Blog     # ⚠️ Deprecated, gunakan ./fusion
```

**Timeline:**

- **Versi 1.x**: Legacy commands masih didukung dengan warning
- **Versi 2.0**: Legacy commands akan dihapus sepenuhnya

## 🔄 Migration Guide

### Dari Flexify ke Fusion

- Semua kode Flexify akan berjalan tanpa perubahan
- CLI commands tetap sama
- Fitur baru tersedia secara otomatis

### Dari Flight (HMF) ke Fusion

- Gunakan `./flexify hmf` untuk perintah yang familiar
- Core classes sudah enhanced dengan fitur Flexify
- Migrasi bertahap ke fitur Fusion yang lebih advanced

---

**Fusion Framework** - Hasil penggabungan **Flexify** + **Flight (HMF)** dengan ❤️ untuk developer PHP Indonesia

**Status: Production Ready & Best of Both Worlds!** 🚀✨

---

## 📚 Quick Reference

### CLI Commands Summary

```bash
# Development (Recommended)
./fusion serve                    # Start server
./fusion migrate                  # Run migrations

# Code Generation (Recommended)
./fusion make:module Blog         # Create module
./fusion make:controller Post     # Create controller

# Project Management (Recommended)
./fusion new blog my-blog         # Create new project
./fusion benchmark                # Run benchmarks
./fusion plugin:list              # List plugins

# Legacy Commands (Deprecated)
./flexify serve                   # ⚠️ Deprecated
./flexify hmf make:controller Post # ⚠️ Deprecated
```

### Core Classes Usage

```php
// Controller
class MyController extends Controller {
    public function index() {
        return $this->view('module.view', $data);  // Flexify style
        $this->render('module/view', $data);       // Flight style
    }
}

// Model
class MyModel extends Model {
    // Both styles work
    MyModel::all();           // Flexify style
    MyModel::findAll();       // Flight style
}

// Service
class MyService extends Service {
    // Enhanced with Flight validation
    $errors = $this->validate($data, $rules);
    $clean = $this->sanitize($data);
}
```

Happy coding with Fusion Framework! 🚀
