<?php

declare(strict_types=1);

use App\Modules\Post\Http\Controllers\CommentController;
use App\Modules\Post\Http\Controllers\PostController;
use App\Modules\Post\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;

/*
| Post module routes (prefix: /api/v1) — feed, posts, comments, reactions.
*/

Route::middleware('auth.jwt')->group(function () {
    // Feed + a user's profile posts (declared before /posts/{post} so the
    // literal "user" segment isn't captured as a post id).
    Route::get('posts', [PostController::class, 'index'])->name('api.posts.feed');
    Route::get('posts/user/{user}', [PostController::class, 'userPosts'])
        ->whereNumber('user')->name('api.posts.by-user');

    Route::post('posts', [PostController::class, 'store'])->name('api.posts.store');
    Route::get('posts/{post}', [PostController::class, 'show'])->whereNumber('post')->name('api.posts.show');
    Route::patch('posts/{post}', [PostController::class, 'update'])->whereNumber('post')->name('api.posts.update');
    Route::delete('posts/{post}', [PostController::class, 'destroy'])->whereNumber('post')->name('api.posts.destroy');

    // Comments
    Route::get('posts/{post}/comments', [CommentController::class, 'index'])->whereNumber('post')->name('api.posts.comments.index');
    Route::post('posts/{post}/comments', [CommentController::class, 'store'])->whereNumber('post')->name('api.posts.comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->whereNumber('comment')->name('api.comments.destroy');

    // Reactions (posts + comments)
    Route::post('posts/{post}/reactions', [ReactionController::class, 'reactToPost'])->whereNumber('post')->name('api.posts.react');
    Route::delete('posts/{post}/reactions', [ReactionController::class, 'unreactFromPost'])->whereNumber('post')->name('api.posts.unreact');
    Route::post('comments/{comment}/reactions', [ReactionController::class, 'reactToComment'])->whereNumber('comment')->name('api.comments.react');
    Route::delete('comments/{comment}/reactions', [ReactionController::class, 'unreactFromComment'])->whereNumber('comment')->name('api.comments.unreact');
});
