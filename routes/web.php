<?php

declare(strict_types=1);

use Gingerminds\LaravelCore\Http\Controllers\Security\AuthController;
use Gingerminds\LaravelCore\Resolver\ResourceResolver;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->get(config('gingerminds-core.health_check_path'), function () {
        return response()->json(['status' => 'ok']);
    })
    ->name('gingerminds-core.health-check');

Route::middleware('web')
    ->prefix(config('gingerminds-core.admin_prefix'))
    ->name('gingerminds-core.')
    ->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::get('login', 'login')->name('login');
            Route::post('login', 'authenticate')->name('authenticate');
        });

        Route::middleware(['gingerminds-core.auth'])->group(function () {
            Route::controller(AuthController::class)->group(function () {
                Route::post('logout', 'logout')->name('logout');
            });

            Route::controller(ResourceResolver::controller('user'))->name('profile.')->group(function () {
                Route::get('profile', 'editProfile')->name('edit-profile');
                Route::patch('profile', 'updateProfile')->name('update-profile');
            });

            Route::resource('users', ResourceResolver::controller('user'));
            Route::resource('contributors', ResourceResolver::controller('contributor'));
            Route::resource('roles', ResourceResolver::controller('role'));
            Route::resource('permissions', ResourceResolver::controller('permission'));
        });
    });
