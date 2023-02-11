# Avocado Project
<hr>
Avocado is easy to learn and use framework for PHP8.1 or higher. Heavily inspired by Spring Boot.

## Goals

- Easy and fast way to create applications in PHP.
- Easy extensible with new features.
- OOP based application.
- High abstraction level especially database connection is only a detail.
- Testable applications.
- Easy configured app.
- Fully use PHP attributes from PHP8.0.

## Features
<hr>

- Rest controllers.
- ORM.
- Configuration properties in `application.{yaml,json}` file.
- Database connection is only a detail.
- Easy writing integration tests.
- Easy file uploading.
- JSON serializing and deserializing.
- Many allowed databases - to connect you only need to set driver class.
- Middleware.
- Custom attributes (annotations) interceptors.
- Parsing request body, params, query, files, attributes into variables (also objects).
- Fully support for enums.
- Abstraction level for database.
- Errors and exceptions handlers.
- Dependency injection.

## Is Avocado for you?
<hr>

If you like Java environment (especially Spring boot), OOP, fast 
development process or fully tests application Avocado is for you.

## First app
<hr>

You need to redirect all request into main file. In Apache, you can do it
in `.htaccess` file like this:
```apacheconf
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)$ /index.php/$1 [L,QSA]
```

or in NGINX
```apacheconf
server {
    listen 80 default_server;

    location / {
            rewrite ^ /index.php last
    }
}
```

Quick start
```php
class Message {
    public function __construct(private string $message){}
}

#[RestController]
class GrettingController {

    
    // response will be JSON { "message": "Hello, <name>!" }
    #[GetMapping("/gretting/:name")]
    public function greet(#[RequestParam(name: "name", defaulValue: "John")] string $name): Message {
        return new Message("Hello, " . $name . "!");
    }
    
}

#[Avocado]
class DemoApplication {

    public static function run(): void {
        Application::run(__DIR__);
    }
    
}

DemoApplication::run();
```

## Plans for future
<hr>

- Create a similar system for JPA repositories.
- Caching data in Redis database.

## License
<hr>

Open Source on Apache2.0