<?php
use Illuminate\Support\Facades\Route;
Route::any('/debug-headers', function (Illuminate\Http\Request $r) {
    return response()->json([
        'accept' => $r->headers->get('Accept'),
        'xrw' => $r->headers->get('X-Requested-With'),
        'expectsJson' => $r->expectsJson(),
    ]);
});
