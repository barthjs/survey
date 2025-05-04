<?php

declare(strict_types=1);

use App\Http\Middleware\AdminMiddleware;
use App\Livewire\Pages\Profile;
use App\Livewire\Pages\Survey\CreateSurvey;
use App\Livewire\Pages\Survey\EditSurvey;
use App\Livewire\Pages\Survey\IndexSurveys;
use App\Livewire\Pages\Survey\ViewSurvey;
use App\Livewire\Pages\User\CreateUser;
use App\Livewire\Pages\User\EditUser;
use App\Livewire\Pages\User\IndexUsers;
use App\Livewire\Pages\User\ViewUser;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('surveys.index');
    }

    return view('pages.homepage');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', Profile::class)
        ->name('profile');

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/users', IndexUsers::class)
            ->name('users.index');

        Route::get('/users/create', CreateUser::class)
            ->name('users.create');

        Route::get('/users/{id}/edit', EditUser::class)
            ->name('users.edit');

        Route::get('/users/{id}', ViewUser::class)
            ->name('users.view');
    });

    Route::get('/surveys', IndexSurveys::class)
        ->name('surveys.index');

    Route::get('/surveys/create', CreateSurvey::class)
        ->name('surveys.create');

    Route::get('/surveys/{id}/edit', EditSurvey::class)
        ->name('surveys.edit');

    Route::get('/surveys/{id}', ViewSurvey::class)
        ->name('surveys.view');
});
