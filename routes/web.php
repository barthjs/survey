<?php

declare(strict_types=1);

use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Profile;
use App\Livewire\Pages\Survey\CreateSurvey;
use App\Livewire\Pages\Survey\EditSurvey;
use App\Livewire\Pages\Survey\IndexSurveys;
use App\Livewire\Pages\Survey\ViewSurvey;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.homepage');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)
        ->name('dashboard');

    Route::get('/profile', Profile::class)
        ->name('profile');

    Route::get('/surveys', IndexSurveys::class)
        ->name('surveys.index');

    Route::get('/surveys/create', CreateSurvey::class)
        ->name('surveys.create');

    Route::get('/surveys/{id}/edit', EditSurvey::class)
        ->name('surveys.edit');

    Route::get('/surveys/{id}', ViewSurvey::class)
        ->name('surveys.view');
});
