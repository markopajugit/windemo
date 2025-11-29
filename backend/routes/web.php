<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

// Serve storage files (fallback for Windows symlink issues)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $file = file_get_contents($filePath);
    $type = mime_content_type($filePath);
    
    return response($file, 200)
        ->header('Content-Type', $type)
        ->header('Cache-Control', 'public, max-age=31536000');
})->where('path', '.*');

// Serve SPA for all other routes (frontend)
// This catches all routes that don't match API or storage
Route::get('/{any?}', function () {
    $indexPath = public_path('index.html');
    
    if (File::exists($indexPath)) {
        return File::get($indexPath);
    }
    
    // Fallback to Laravel welcome page if SPA not built
    return view('welcome');
})->where('any', '^(?!api).*$');
