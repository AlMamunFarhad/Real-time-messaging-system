<?php

// Manual autoloader for Modules as a workaround for composer dump-autoload issues
spl_autoload_register(function ($class) {
    if (strpos($class, 'Modules\\') === 0) {
        $parts = explode('\\', $class);
        if (count($parts) >= 2) {
            $module = $parts[1];
            
            // Try standard PSR-4 path (e.g. Modules/Name/app/...)
            $pathWithApp = 'Modules/' . $module . '/app/' . implode('/', array_slice($parts, 2));
            $fileWithApp = __DIR__ . '/../' . $pathWithApp . '.php';
            if (file_exists($fileWithApp)) {
                require_once $fileWithApp;
                return;
            }

            // Try alternate path (e.g. Modules/Name/...)
            $pathWithoutApp = 'Modules/' . implode('/', array_slice($parts, 1));
            $fileWithoutApp = __DIR__ . '/../' . $pathWithoutApp . '.php';
            if (file_exists($fileWithoutApp)) {
                require_once $fileWithoutApp;
                return;
            }
        }
    }
});



use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        channels: __DIR__ . '/../routes/channels.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Modules\Messaging\Http\Middleware\TrackOnlineStatus::class,
        ]);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
