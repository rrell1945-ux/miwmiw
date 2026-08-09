<?php

// Folder temporary yang bisa ditulis oleh Vercel
$dirs = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/framework',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Paksa Laravel menggunakan folder writable Vercel
putenv('VIEW_COMPILED_PATH=/tmp/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/views';

require __DIR__ . '/../public/index.php';