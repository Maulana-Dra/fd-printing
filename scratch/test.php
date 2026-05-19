<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot framework so DB connection is established
$kernel->bootstrap();

$user = App\Models\User::find(1);
if ($user) {
    Auth::login($user);
}

$request = Illuminate\Http\Request::create('/admin/products', 'GET');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() >= 500) {
    if (isset($response->exception)) {
        echo "Exception: " . $response->exception->getMessage() . "\n";
        echo "File: " . $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
        echo $response->exception->getTraceAsString() . "\n";
    } else {
        echo "Content: " . substr($response->getContent(), 0, 1000);
    }
}
