<?php

declare(strict_types=1);

use App\Http\Middleware\AdminMiddleware;
use App\Livewire\Pages\Profile;
use App\Livewire\Pages\Survey\CreateSurvey;
use App\Livewire\Pages\Survey\EditSurvey;
use App\Livewire\Pages\Survey\IndexSurveys;
use App\Livewire\Pages\Survey\SubmitSurvey;
use App\Livewire\Pages\Survey\ViewResponse;
use App\Livewire\Pages\Survey\ViewSurvey;
use App\Livewire\Pages\User\IndexUsers;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('surveys.index');
    }

    return view('pages.homepage');
})->name('home');

$middlewares = ['auth'];

if (config('app.enable_email_verification')) {
    $middlewares[] = 'verified';
}

Route::middleware($middlewares)->group(function () {
    Route::get('/language/{locale}', function ($locale) {
        if (array_key_exists($locale, config('app.locales'))) {
            session()->put('locale', $locale);

            return redirect()->back();
        }

        abort(404);
    })->name('locale');

    Route::get('/profile', Profile::class)
        ->name('profile');

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/users', IndexUsers::class)
            ->name('users.index');
    });

    Route::get('/surveys', IndexSurveys::class)
        ->name('surveys.index');

    Route::get('/surveys/create', CreateSurvey::class)
        ->name('surveys.create');

    Route::get('/surveys/{id}/edit', EditSurvey::class)
        ->name('surveys.edit');

    Route::get('/surveys/{id}', ViewSurvey::class)
        ->name('surveys.view');

    Route::get('/surveys/response/{id}', ViewResponse::class)
        ->name('surveys.response');
});

Route::get('/results/{id}', ViewSurvey::class)
    ->name('surveys.public.view');

Route::get('/s/{id}', SubmitSurvey::class)
    ->name('surveys.submit');

Route::get('thank-you', function () {
    return view('pages.thank-you');
})->name('surveys.thank-you');
