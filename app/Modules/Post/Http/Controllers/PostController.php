<?php

declare(strict_types=1);

namespace App\Modules\Post\Http\Controllers;

use App\Modules\Post\Http\Requests\StorePostRequest;
use App\Modules\Post\Http\Requests\UpdatePostRequest;
use App\Modules\Post\Http\Resources\PostResource;
use App\Modules\Post\Services\PostService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends ApiController
{
    public function __construct(private readonly PostService $posts)
    {
    }

    /** Home feed: the viewer's own posts + their friends'. */
    public function index(Request $request): JsonResponse
    {
        $feed = $this->posts->feed($request->user(), (int) $request->integer('per_page', 15));

        return $this->ok(PostResource::collection($feed));
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->posts->create($request->user(), $request->validated(), $request->file('media') ?: []);

        return $this->created(new PostResource($post), 'Post published.');
    }

    public function show(Request $request, int $post): JsonResponse
    {
        return $this->ok(new PostResource($this->posts->show($request->user(), $post)));
    }

    public function update(UpdatePostRequest $request, int $post): JsonResponse
    {
        $updated = $this->posts->update($request->user(), $post, $request->validated());

        return $this->ok(new PostResource($updated), 'Post updated.');
    }

    public function destroy(Request $request, int $post): JsonResponse
    {
        $this->posts->delete($request->user(), $post);

        return $this->ok(message: 'Post deleted.');
    }

    /** Posts on a given user's profile (visibility-filtered for the viewer). */
    public function userPosts(Request $request, int $user): JsonResponse
    {
        $posts = $this->posts->byAuthor($request->user(), $user, (int) $request->integer('per_page', 15));

        return $this->ok(PostResource::collection($posts));
    }
}
