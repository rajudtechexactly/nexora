<?php

declare(strict_types=1);

namespace App\Modules\Post\Http\Controllers;

use App\Modules\Post\Http\Requests\StoreCommentRequest;
use App\Modules\Post\Http\Resources\CommentResource;
use App\Modules\Post\Services\CommentService;
use App\Modules\Post\Services\PostService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends ApiController
{
    public function __construct(
        private readonly CommentService $comments,
        private readonly PostService $posts,
    ) {
    }

    public function index(Request $request, int $post): JsonResponse
    {
        // Authorises that the viewer may see the post (404s otherwise).
        $this->posts->show($request->user(), $post);

        $comments = $this->comments->list($post, $request->user()->id, (int) $request->integer('per_page', 20));

        return $this->ok(CommentResource::collection($comments));
    }

    public function store(StoreCommentRequest $request, int $post): JsonResponse
    {
        $authorisedPost = $this->posts->show($request->user(), $post);

        $comment = $this->comments->create(
            $request->user(),
            $authorisedPost,
            (string) $request->string('content'),
            $request->filled('parent_id') ? (int) $request->integer('parent_id') : null,
        );

        return $this->created(new CommentResource($comment), 'Comment added.');
    }

    public function destroy(Request $request, int $comment): JsonResponse
    {
        $this->comments->delete($request->user(), $comment);

        return $this->ok(message: 'Comment deleted.');
    }
}
