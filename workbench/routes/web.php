<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Workbench\App\Models\Page;

Route::get('/', fn () => view('pages.index', [
    'pages' => Page::query()->orderBy('id')->get(),
]))->name('pages.index');

Route::get('/pages/{page:slug}', fn (Page $page) => view('pages.show', [
    'page' => $page,
]))->name('pages.show');
