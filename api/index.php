<?php

$storage = '/tmp/laravel-storage';

$dirs = [
    $storage,
    $storage . '/framework',
    $storage . '/framework/cache',
    $storage . '/framework/sessions',
    $storage . '/framework/views',
    $storage . '/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Laravel storage ke folder writable Vercel
putenv("APP_STORAGE={$storage}");
$_ENV['APP_STORAGE'] = $storage;
$_SERVER['APP_STORAGE'] = $storage;

// View cache ke /tmp
putenv('VIEW_COMPILED_PATH=' . $storage . '/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = $storage . '/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = $storage . '/framework/views';

// Log ke stderr supaya tidak menulis storage/logs
putenv('LOG_CHANNEL=stderr');
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';

require __DIR__ . '/../public/index.php';