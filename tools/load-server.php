<?php
// Dedicated loopback-only test router. All persistence is forced to the isolated fixture directory.
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
require __DIR__.'/../backend/vendor/autoload.php';
$app = require __DIR__.'/../backend/bootstrap/app.php';
$directory = realpath(__DIR__.'/../backend/storage/app/private/load-test');
if (!$directory || !is_file($directory.'/load-test.sqlite')) { http_response_code(503); exit('Prepare the isolated load-test database first.'); }
$app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) use ($directory) {
    $app['config']->set([
        'app.env' => 'testing', 'app.debug' => false,
        'database.default' => 'sqlite', 'database.connections.sqlite.database' => $directory.'/load-test.sqlite',
        'database.connections.sqlite.url' => null, 'database.connections.sqlite.busy_timeout' => 30000,
        'cache.default' => 'file', 'cache.stores.file.path' => $directory.'/cache',
        'mail.default' => 'array', 'services.gemini.api_key' => null,
    ]);
});
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
