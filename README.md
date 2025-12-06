# PHP Mini Framework

A lightweight, modern PHP MVC framework designed for simplicity, performance, and ease of use. Perfect for small to medium-sized projects where you need structure without the bloat of larger frameworks.

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Project Structure](#-project-structure)
- [Basic Usage](#-basic-usage)
- [Routing](#-routing)
- [Controllers](#-controllers)
- [Views](#-views)
- [Models](#-models)
- [Middleware](#-middleware)
- [Services](#-services)
- [Security](#-security)
- [Caching](#-caching)
- [Logging](#-logging)
- [Cron Jobs](#-cron-jobs)
- [Debugging](#-debugging)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [API Reference](#-api-reference)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features

- **🚀 Lightweight & Fast**: Minimal overhead, optimized for performance
- **🏗️ MVC Architecture**: Clean separation of concerns
- **🛣️ Advanced Routing**: Route grouping, middleware support, parameter binding
- **🛡️ Built-in Security**: CSRF protection, rate limiting, input sanitization
- **💾 Multiple Database Support**: MySQL, SQLite ready
- **📦 Service Container**: Dependency management made easy
- **⚡ Built-in Caching**: File-based caching system
- **📝 Comprehensive Logging**: Daily log files with multiple levels
- **🔄 Job Queue**: Simple job scheduling system
- **🔧 Debug Tools**: Built-in syntax checker and error handler
- **🌐 REST API Ready**: JSON responses, API rate limiting
- **🎨 Template Engine**: Simple PHP-based views with layouts

## 📋 Requirements

- PHP 7.4 or higher
- PDO extension
- JSON extension
- Composer
- Web server (Apache/Nginx) or PHP built-in server

## 🚀 Installation

### Via Composer (Recommended)

```bash
# Create new project
composer create-project farhamaghdasi/mini-framework my-project

# Navigate to project
cd my-project

# Install dependencies
composer install

# Set up environment
cp .env.example .env

# Create required directories
mkdir -p storage/{logs,cache,sessions,views}
mkdir -p resources/views/{layouts,errors}
```

### Manual Installation

```bash
# Clone repository
git clone https://github.com/farhamaghdasi/php-mini-framework.git
cd php-mini-framework

# Install dependencies
composer install

# Configure environment
cp .env.example .env
```

## ⚙️ Configuration

### Environment Variables

Edit `.env` file:

```env
# App Configuration
APP_NAME="My Application"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=root
DB_PASSWORD=

# Security
CRON_TOKEN=your-secret-cron-token-here
SESSION_SECRET=your-session-secret-here

# Logging
LOG_LEVEL=debug
LOG_SYSLOG=false

# Cache
CACHE_DRIVER=file
CACHE_LIFETIME=3600

# View
VIEW_CACHE=false
```

### Web Server Configuration

#### Apache (.htaccess included)
Ensure `mod_rewrite` is enabled and set document root to `public/` directory.

#### Nginx
```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/project/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

#### PHP Built-in Server (Development)
```bash
php -S localhost:8000 -t public
```

## 📁 Project Structure

```
php-mini-framework/
├── app/
│   ├── Controllers/          # Application controllers
│   ├── Core/                # Framework core classes
│   │   ├── Application.php
│   │   ├── Router.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── helpers.php
│   ├── Jobs/                # Background jobs
│   ├── Middlewares/         # HTTP middleware
│   ├── Models/              # Database models
│   ├── Services/            # Service classes
│   └── routes/              # Route definitions
├── bootstrap/               # Application bootstrap
├── config/                  # Configuration files
├── public/                  # Public assets & index.php
├── resources/               # Views and assets
│   └── views/               # Template files
├── storage/                 # Logs, cache, sessions
│   ├── logs/
│   ├── cache/
│   ├── sessions/
│   └── views/
├── vendor/                  # Composer dependencies
├── .env.example             # Environment example
├── composer.json            # Dependencies
├── index.php               # Application entry point
└── README.md               # This file
```

## 🎯 Basic Usage

### Creating Your First Route

Edit `app/routes/web.php`:

```php
use App\Controllers\HomeController;

// Basic route
$router->get('/', [HomeController::class, 'index']);

// Route with parameters
$router->get('/user/{id}', [HomeController::class, 'showUser']);

// POST route
$router->post('/contact', [HomeController::class, 'storeContact']);
```

### Creating a Controller

```php
<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class UserController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $users = [/* fetch users */];
        $this->render('users/index', ['users' => $users]);
    }

    public function showUser(Request $request, Response $response, array $params)
    {
        $userId = $params['id'];
        // Your logic here
        $this->json(['user_id' => $userId]);
    }
}
```

## 🛣️ Routing

### Basic Routes

```php
// GET route
$router->get('/about', [HomeController::class, 'about']);

// POST route
$router->post('/submit', [HomeController::class, 'submit']);

// PUT route
$router->put('/update/{id}', [HomeController::class, 'update']);

// DELETE route
$router->delete('/delete/{id}', [HomeController::class, 'delete']);

// Multiple methods
$router->match(['GET', 'POST'], '/contact', [HomeController::class, 'contact']);
```

### Route Groups

```php
// Group with prefix
$router->group('/admin', function($router) {
    $router->get('/dashboard', [AdminController::class, 'dashboard']);
    $router->get('/users', [AdminController::class, 'users']);
});

// Group with middleware
$router->group('/api', function($router) {
    $router->get('/data', [ApiController::class, 'getData']);
    $router->post('/submit', [ApiController::class, 'submitData']);
}, [RateLimitMiddleware::class]);
```

### Route Parameters

```php
// Required parameter
$router->get('/user/{id}', [UserController::class, 'show']);

// Multiple parameters
$router->get('/post/{year}/{month}/{slug}', [PostController::class, 'show']);
```

## 🎮 Controllers

### Base Controller

All controllers extend `App\Controllers\Controller`:

```php
<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class ExampleController extends Controller
{
    public function example(Request $request, Response $response)
    {
        // Access services
        $this->logger->info('Example action called');
        
        // Render view
        $this->render('example/index', ['title' => 'Example']);
        
        // JSON response
        $this->json(['success' => true, 'data' => []]);
        
        // Redirect
        $this->redirect('/home');
        
        // Validate input
        $errors = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
    }
}
```

### Available Methods

- `render($view, $data)` - Render a view template
- `json($data, $statusCode)` - Return JSON response
- `redirect($url, $statusCode)` - Redirect to URL
- `validate($request, $rules)` - Validate request data

## 👁️ Views

### Creating Views

Create files in `resources/views/`:

**`resources/views/home/index.php`:**
```php
<?php $layout = 'layouts/main'; ?>
<?php $title = 'Home Page'; ?>

<h1>Welcome, <?= $this->escape($name) ?></h1>
<p>This is the home page.</p>

<?php if (!empty($items)): ?>
    <ul>
        <?php foreach ($items as $item): ?>
            <li><?= $this->escape($item) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
```

**`resources/views/layouts/main.php`:**
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Mini Framework' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/about">About</a>
        </nav>
    </header>
    
    <main>
        <?= $content ?>
    </main>
    
    <footer>
        &copy; <?= date('Y') ?> My App
    </footer>
</body>
</html>
```

### Helper Functions

```php
// In controllers
$this->render('home/index', ['name' => 'John']);

// Using helper function
echo view('home/index', ['name' => 'John']);

// Escaping output
$this->escape($userInput);
$this->e($userInput); // Alias
```

## 💾 Models

### Base Model

```php
<?php
namespace App\Models;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    // Additional methods
    public function findByEmail($email)
    {
        return $this->all(['email = ?'], [$email])[0] ?? null;
    }
}
```

### Basic CRUD Operations

```php
$user = new User();

// Create
$id = $user->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_BCRYPT)
]);

// Read
$user = $user->find($id);
$allUsers = $user->all(['active = ?'], [1]);

// Update
$updated = $user->update($id, ['name' => 'Jane Doe']);

// Delete
$deleted = $user->delete($id);
```

### Direct Database Access

```php
$db = \App\Models\Database::getInstance();

// Query with parameters
$stmt = $db->query('SELECT * FROM users WHERE active = ?', [1]);
$users = $stmt->fetchAll();

// Transactions
$db->beginTransaction();
try {
    $db->query('INSERT INTO users (name) VALUES (?)', ['John']);
    $db->query('INSERT INTO logs (message) VALUES (?)', ['User created']);
    $db->commit();
} catch (\Exception $e) {
    $db->rollback();
    throw $e;
}
```

## 🛡️ Middleware

### Creating Middleware

```php
<?php
namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request, Response $response): bool
    {
        if (!isset($_SESSION['user_id'])) {
            $response->redirect('/login');
            return false;
        }
        
        return true;
    }
}
```

### Built-in Middleware

1. **CsrfMiddleware** - CSRF protection for forms
2. **RateLimitMiddleware** - API rate limiting

### Using Middleware

```php
// Route-specific middleware
$router->get('/profile', [UserController::class, 'profile'], [AuthMiddleware::class]);

// Group middleware
$router->group('/admin', function($router) {
    // admin routes
}, [AuthMiddleware::class, 'AdminMiddleware']);
```

## 🔧 Services

### Available Services

- **Cache** - File-based caching
- **Logger** - Logging with multiple levels
- **Security** - Security utilities
- **View** - Template rendering

### Using Services

```php
// In controllers (auto-injected)
$this->cache->set('key', 'value', 3600);
$this->logger->info('Action performed');
$hashed = $this->security->hashPassword('password');

// Using helper functions
cache('key', 'value', 3600);
logger()->info('Message');
session('user_id', 123);
```

### Custom Services

Register in `Application::initServices()`:

```php
$this->services['mailer'] = new Mailer($this->config['mail']);
```

## 🛡️ Security

### CSRF Protection

```php
// In form
<input type="hidden" name="_token" value="<?= $this->security->generateCsrfToken() ?>">

// In controller
$token = $request->input('_token');
if (!$this->security->verifyCsrfToken($token, $this->session)) {
    // Invalid token
}
```

### Input Validation

```php
$errors = $this->security->validate($request->all(), [
    'name' => 'required|min:3|max:50',
    'email' => 'required|email',
    'password' => 'required|min:8|confirmed',
    'age' => 'numeric|min:18'
]);

if (empty($errors)) {
    // Validation passed
}
```

### Password Hashing

```php
$hashed = $this->security->hashPassword('plain_password');
$isValid = $this->security->verifyPassword('plain_password', $hashed);
```

## 💾 Caching

### Basic Usage

```php
// Store cache for 1 hour
$this->cache->set('user_123', $userData, 3600);

// Retrieve
$userData = $this->cache->get('user_123');

// Check if exists
if ($this->cache->has('user_123')) {
    // ...
}

// Delete
$this->cache->delete('user_123');

// Clear all cache
$this->cache->clear();
```

### Cache Helper

```php
// Using helper function
cache('key', 'value'); // Set
$value = cache('key'); // Get
cache('key', false);   // Delete
```

## 📝 Logging

### Log Levels

```php
$this->logger->debug('Debug message');
$this->logger->info('Info message');
$this->logger->warning('Warning message');
$this->logger->error('Error message');
```

### With Context

```php
$this->logger->error('User login failed', [
    'user_id' => 123,
    'ip' => $request->getIp(),
    'attempts' => 3
]);
```

### Log Helper

```php
logger()->info('Message');
```

## ⏰ Cron Jobs

### Setting Up Cron

```bash
# Add to crontab
* * * * * curl -X POST http://yourdomain.com/cron/run-job \
  -H "X-Cron-Token: your-secret-token" \
  -d "job=example"
```

### Creating Jobs

```php
<?php
namespace App\Jobs;

class CleanupJob
{
    public function handle(): array
    {
        // Cleanup logic
        return ['status' => 'success', 'cleaned' => 10];
    }
}
```

### Job Controller

```php
public function runJob(Request $request, Response $response)
{
    $token = $request->getHeader('X-Cron-Token');
    $validToken = $_ENV['CRON_TOKEN'] ?? '';
    
    if (!$token || $token !== $validToken) {
        return $this->json(['error' => 'Unauthorized'], 401);
    }
    
    $jobName = $request->input('job');
    
    switch ($jobName) {
        case 'cleanup':
            $job = new CleanupJob();
            $result = $job->handle();
            break;
        // ...
    }
    
    $this->json(['success' => true, 'result' => $result]);
}
```

## 🔍 Debugging

### Syntax Checking

```bash
# Check all project files
php check-syntax.php

# Check core files only
php check-core.php
```

### Debug Mode

Enable in `.env`:
```env
APP_DEBUG=true
APP_ENV=development
```

### Error Handling

Errors are logged to `storage/logs/`:
- Daily log files
- Debug information in development
- JSON error responses for APIs

### Debug Helper

```php
// Dump and die
dd($variable1, $variable2);

// Check configuration
config('app.debug'); // Returns debug setting
```

## 🧪 Testing

### Running Tests

```bash
# Install PHPUnit
composer require --dev phpunit/phpunit

# Run tests
vendor/bin/phpunit
```

### Example Test

```php
<?php
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    public function testIndexReturnsView()
    {
        $controller = new HomeController();
        // Test logic
        $this->assertTrue(true);
    }
}
```

## 🚀 Deployment

### Production Checklist

1. **Update `.env`:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Set proper permissions:**
   ```bash
   chmod -R 755 storage
   chmod -R 755 public
   chmod 644 .env
   ```

3. **Optimize Composer:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Configure Web Server:**
   - Set document root to `public/`
   - Enable HTTPS
   - Configure proper headers

5. **Set up Cron Jobs:**
   ```bash
   crontab -e
   ```

### Performance Optimization

- Enable OPCache
- Use CDN for assets
- Configure browser caching
- Enable compression

## 📚 API Reference

### Core Classes

#### Application
```php
$app = Application::getInstance();
$app->setBasePath('/path');
$app->getService('cache');
$app->run();
```

#### Request
```php
$request->getMethod();
$request->getPath();
$request->input('key');
$request->all();
$request->isAjax();
$request->json();
```

#### Response
```php
$response->json($data);
$response->html($content);
$response->redirect($url);
$response->setStatusCode(404);
```

#### Router
```php
$router->get($path, $handler);
$router->post($path, $handler);
$router->group($prefix, $callback);
```

### Helper Functions

```php
app();          // Get application instance
config($key);   // Get configuration
view($template, $data); // Render view
redirect($url); // Redirect
session($key, $value); // Session access
cache($key, $value); // Cache access
logger();       // Get logger instance
env($key);      // Get environment variable
dd($var);       // Dump and die
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write tests
5. Submit a pull request

### Development Setup

```bash
git clone https://github.com/farhamaghdasi/php-mini-framework.git
cd php-mini-framework
composer install
cp .env.example .env
```

### Code Style

- Follow PSR-12 coding standards
- Add PHPDoc comments
- Write meaningful commit messages
- Include tests for new features

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.