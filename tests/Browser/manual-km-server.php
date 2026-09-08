<?php

// Test-only PHP HTTP server: never include this from production routes.
// Run inside a disposable container with MANUAL_KM_TEST_ROOT=/tmp/manual-km-browser-<unique>.
// Override the image's production entrypoint; see manual-km-upload.README.md.
// php tests/Browser/manual-km-server.php --init
// php -S 0.0.0.0:8000 -t public tests/Browser/manual-km-server.php

use App\Models\CompetitionCategory;
use App\Models\KnowledgeItem;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

if (! in_array(PHP_SAPI, ['cli', 'cli-server'], true)) {
    http_response_code(404);
    exit;
}

$testRoot = getenv('MANUAL_KM_TEST_ROOT');
if (! is_string($testRoot) || ! preg_match('~^/tmp/manual-km-browser-[a-zA-Z0-9_-]+$~D', $testRoot)) {
    throw new RuntimeException('An isolated /tmp/manual-km-browser-<unique> root is required.');
}

$initializing = PHP_SAPI === 'cli' && ($argv[1] ?? '') === '--init';
if ($initializing) {
    if (file_exists($testRoot)) {
        throw new RuntimeException('Refusing to reuse an existing test root; choose a fresh name.');
    }
    foreach (['', '/storage/app/private', '/storage/app/public', '/storage/framework/cache/data', '/storage/framework/sessions', '/storage/framework/views', '/storage/logs'] as $directory) {
        mkdir($testRoot.$directory, 0700, true);
    }
    touch($testRoot.'/database.sqlite');
    file_put_contents($testRoot.'/key', 'base64:'.base64_encode(random_bytes(32)));
}
if (! is_file($testRoot.'/key') || ! is_file($testRoot.'/database.sqlite')) {
    throw new RuntimeException('Initialize the isolated server first.');
}

// Override all stateful defaults before Laravel reads configuration. In particular,
// ignore both .env and the normal config/routes cache, so no live DB can be selected.
foreach ([
    'APP_ENV' => 'browser-regression',
    'APP_DEBUG' => 'false',
    'APP_KEY' => file_get_contents($testRoot.'/key'),
    'APP_URL' => getenv('MANUAL_KM_TEST_ORIGIN') ?: 'http://127.0.0.1:18081',
    'APP_CONFIG_CACHE' => $testRoot.'/config.php',
    'APP_ROUTES_CACHE' => $testRoot.'/routes.php',
    'APP_SERVICES_CACHE' => $testRoot.'/services.php',
    'APP_PACKAGES_CACHE' => $testRoot.'/packages.php',
    'APP_EVENTS_CACHE' => $testRoot.'/events.php',
    'LARAVEL_STORAGE_PATH' => $testRoot.'/storage',
    'VIEW_COMPILED_PATH' => $testRoot.'/storage/framework/views',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $testRoot.'/database.sqlite',
    'DB_URL' => '',
    'SESSION_DRIVER' => 'file',
    'SESSION_COOKIE' => 'manual_km_browser_regression',
    'SESSION_SECURE_COOKIE' => 'false',
    'SESSION_DOMAIN' => 'null',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
    'FILESYSTEM_DISK' => 'local',
    'LOG_CHANNEL' => 'single',
    'BCRYPT_ROUNDS' => '4',
] as $name => $value) {
    putenv($name.'='.$value);
    $_ENV[$name] = $_SERVER[$name] = $value;
}

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->useEnvironmentPath($testRoot);
$app->loadEnvironmentFrom('.env.manual-km-browser-unused');
$app->useStoragePath($testRoot.'/storage');
$app->make(Kernel::class)->bootstrap();
$app->make(Vite::class)->useHotFile($testRoot.'/unused-vite-hot');

if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $testRoot.'/database.sqlite'
    || config('filesystems.disks.local.root') !== $testRoot.'/storage/app/private'
    || config('filesystems.disks.public.root') !== $testRoot.'/storage/app/public'
    || config('session.files') !== $testRoot.'/storage/framework/sessions'
    || config('view.compiled') !== $testRoot.'/storage/framework/views') {
    throw new RuntimeException('Database/storage isolation failed.');
}

if ($initializing) {
    if (Artisan::call('migrate', ['--force' => true]) !== 0) {
        throw new RuntimeException(Artisan::output());
    }
    foreach (['superadmin' => 'Super Admin', 'competition-admin' => 'Competition Admin'] as $prefix => $roleName) {
        $roleId = DB::table('roles')->insertGetId(['role_name' => $roleName, 'display_name' => $roleName]);
        User::create([
            'role_id' => $roleId,
            'username' => $prefix.'-browser',
            'email' => $prefix.'@browser.example',
            'password' => 'manual-km-browser-password',
            'is_active' => true,
        ]);
    }
    CompetitionCategory::create(['category_name' => 'Browser fixture', 'category_slug' => 'browser-fixture', 'is_active' => true]);
    echo "Isolated Manual KM browser database initialized.\n";
    exit;
}

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($path === '/__manual-km-test/status') {
    header('Content-Type: application/json');
    echo json_encode([
        'isolated' => true,
        'items' => KnowledgeItem::with('creator.role')->get()->map(fn ($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'role' => $item->creator->role->role_name,
            'submission_id' => $item->submission_id,
            'name' => $item->attachment_original_name,
            'path' => $item->attachment_path,
            'sha256' => $item->attachment_path && Storage::disk('local')->exists($item->attachment_path)
                ? hash_file('sha256', Storage::disk('local')->path($item->attachment_path)) : null,
        ]),
        'private_files' => Storage::disk('local')->allFiles(),
        'public_files' => Storage::disk('public')->allFiles(),
    ], JSON_THROW_ON_ERROR);
    exit;
}

// Serve only actual assets under public; every app request uses Laravel's real
// HTTP kernel, session, CSRF, FormRequest, controller, and local storage driver.
$publicRoot = realpath(dirname(__DIR__, 2).'/public');
$asset = realpath($publicRoot.$path);
if ($asset && str_starts_with($asset, $publicRoot.DIRECTORY_SEPARATOR) && is_file($asset)
    && in_array(strtolower(pathinfo($asset, PATHINFO_EXTENSION)), ['css', 'js', 'png', 'jpg', 'jpeg', 'svg', 'webp', 'ico', 'woff', 'woff2'], true)) {
    return false;
}

$app->handleRequest(Request::capture());
