<?php

declare(strict_types=1);

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Workbench\App\Models\Page;

Route::get('/', fn (): Factory | View => view('pages.index', [
    'pages' => Page::query()->orderBy('id')->get(),
]))->name('pages.index');

Route::get('/pages/{page:slug}', fn (Page $page): Factory | View => view('pages.show', [
    'page' => $page,
]))->name('pages.show');
