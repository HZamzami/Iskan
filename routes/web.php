<?php

use Illuminate\Support\Facades\Route;

/*
 * The panel used to live under /admin. Notifications stored before the move
 * still link there, so forward any old /admin/... link to its new path.
 */
Route::get('admin/{path?}', fn (?string $path = null) => redirect('/'.ltrim((string) $path, '/'), 301))
    ->where('path', '.*');
