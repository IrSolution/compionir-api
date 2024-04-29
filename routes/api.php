<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;

Route::controller(AuthController::class)->group(function() {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::group(['middleware' => ['auth:sanctum']], function () {

    //admin panel
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin']], function () {

        //users
        Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [UserController::class, 'trash'])->name('trash');
            Route::get('/restore-all', [UserController::class, 'restoreAll'])->name('restoreAll');
            Route::post('/{id}/restore', [UserController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [UserController::class, 'forceDelete'])->name('forceDelete');
        });

        //articles
        Route::group(['prefix' => 'articles', 'as' => 'articles.'], function () {
            Route::get('/', [ArticleController::class, 'index'])->name('index');
            Route::post('/', [ArticleController::class, 'store'])->name('store');
            Route::put('/{id}', [ArticleController::class, 'update'])->name('update');
            Route::delete('/{id}', [ArticleController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [ArticleController::class, 'trash'])->name('trash');
            Route::get('/restore-all', [ArticleController::class, 'restoreAll'])->name('restoreAll');
            Route::post('/{id}/restore', [ArticleController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [ArticleController::class, 'forceDelete'])->name('forceDelete');
        });

        //categories
        Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [CategoryController::class, 'trash'])->name('trash');
            Route::get('/restore-all', [CategoryController::class, 'restoreAll'])->name('restoreAll');
            Route::post('/{id}/restore', [CategoryController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('forceDelete');
        });

        //tags
        Route::group(['prefix' => 'tags', 'as' => 'tags.'], function () {
            Route::get('/', [TagController::class, 'index'])->name('index');
            Route::post('/', [TagController::class, 'store'])->name('store');
            Route::put('/{id}', [TagController::class, 'update'])->name('update');
            Route::delete('/{id}', [TagController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [TagController::class, 'trash'])->name('trash');
            Route::get('/restore-all', [TagController::class, 'restoreAll'])->name('restoreAll');
            Route::post('/{id}/restore', [TagController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [TagController::class, 'forceDelete'])->name('forceDelete');
        });

        //contacts
        Route::group(['prefix' => 'contacts', 'as' => 'contacts.'], function () {
            Route::get('/', [ContactController::class, 'index'])->name('index');
            Route::delete('/{id}', [ContactController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [ContactController::class, 'trash'])->name('trash');
            Route::get('/restore-all', [ContactController::class, 'restoreAll'])->name('restoreAll');
            Route::post('/{id}/restore', [ContactController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [ContactController::class, 'forceDelete'])->name('forceDelete');
        });

        //projects
        Route::group(['prefix' => 'projects', 'as' => 'projects.'], function () {
            Route::get('/', [ProjectController::class, 'index'])->name('index');
            Route::post('/', [ProjectController::class, 'store'])->name('store');
            Route::put('/{id}', [ProjectController::class, 'update'])->name('update');
            Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [ProjectController::class, 'trash'])->name('trash');
            Route::get('/restore-all', [ProjectController::class, 'restoreAll'])->name('restoreAll');
            Route::post('/{id}/restore', [ProjectController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [ProjectController::class, 'forceDelete'])->name('forceDelete');
        });

        //services
        Route::group(['prefix' => 'services', 'as' => 'services.'], function () {
            Route::get('/', [ServiceController::class, 'index'])->name('index');
            Route::post('/', [ServiceController::class, 'store'])->name('store');
            Route::put('/{id}', [ServiceController::class, 'update'])->name('update');
            Route::delete('/{id}', [ServiceController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [ServiceController::class, 'trash'])->name('trash');
            Route::get('/restore-all', [ServiceController::class, 'restoreAll'])->name('restoreAll');
            Route::post('/{id}/restore', [ServiceController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [ServiceController::class, 'forceDelete'])->name('forceDelete');
        });

        //teams
        Route::group(['prefix' => 'teams', 'as' => 'teams.'], function () {
            Route::get('/', [TeamController::class, 'index'])->name('index');
            Route::post('/', [TeamController::class, 'store'])->name('store');
            Route::put('/{id}', [TeamController::class, 'update'])->name('update');
            Route::delete('/{id}', [TeamController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [TeamController::class, 'trash'])->name('trash');
            Route::get('/restore-all', [TeamController::class, 'restoreAll'])->name('restoreAll');
            Route::post('/{id}/restore', [TeamController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [TeamController::class, 'forceDelete'])->name('forceDelete');
        });

        //testimonials
        Route::group(['prefix' => 'testimonials', 'as' => 'testimonials.'], function () {
            Route::get('/', [TestimonialController::class, 'index'])->name('index');
            Route::post('/', [TestimonialController::class, 'store'])->name('store');
            Route::put('/{id}', [TestimonialController::class, 'update'])->name('update');
            Route::delete('/{id}', [TestimonialController::class, 'destroy'])->name('destroy');
            Route::get('/trash', [TestimonialController::class, 'trash'])->name('trash');
            Route::get('/restore-all', [TestimonialController::class, 'restoreAll'])->name('restoreAll');
            Route::post('/{id}/restore', [TestimonialController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [TestimonialController::class, 'forceDelete'])->name('forceDelete');
        });
    });

    //Profile
    Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar/change', [ProfileController::class, 'avatarChange'])->name('avatar.change');
        Route::post('/password/change', [ProfileController::class, 'passwordChange'])->name('password.change');
    });

});
