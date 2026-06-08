<?php

declare(strict_types=1);

namespace App\Modules\Post\Http\Controllers;

use App\Modules\Post\Http\Requests\ReactionRequest;
use App\Modules\Post\Models\Comment;
use App\Modules\Post\Services\PostService;
use App\Modules\Post\Services\ReactionService;
use App\Modules\Shared\Http\Controllers\ApiController;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends ApiController
{
    public function __construct(
        private readonly ReactionService $reactions,
        private readonly PostService $posts,
    ) {
    }

    public function reactToPost(ReactionRequest $request, int $post): JsonResponse
    {
        $target = $this->posts->show($request->user(), $post);
        $reaction = $this->reactions->react($request->user(), $target, (string) $request->string('type'));

        return $this->ok([
            'type'            => $reaction->type,
            'reactions_count' => (int) $target->fresh()->reactions_count,
        ], 'Reaction saved.');
    }

    public function unreactFromPost(Request $request, int $post): JsonResponse
    {
        $target = $this->posts->show($request->user(), $post);
        $this->reactions->unreact($request->user(), $target);

        return $this->ok(['reactions_count' => (int) $target->fresh()->reactions_count], 'Reaction removed.');
    }

    public function reactToComment(ReactionRequest $request, int $comment): JsonResponse
    {
        $target = $this->loadComment($request->user(), $comment);
        $reaction = $this->reactions->react($request->user(), $target, (string) $request->string('type'));

        return $this->ok([
            'type'            => $reaction->type,
            'reactions_count' => (int) $target->fresh()->reactions_count,
        ], 'Reaction saved.');
    }

    public function unreactFromComment(Request $request, int $comment): JsonResponse
    {
        $target = $this->loadComment($request->user(), $comment);
        $this->reactions->unreact($request->user(), $target);

        return $this->ok(['reactions_count' => (int) $target->fresh()->reactions_count], 'Reaction removed.');
    }

    /** Load a comment and ensure the viewer may see its post. */
    private function loadComment(User $user, int $commentId): Comment
    {
        /** @var Comment $comment */
        $comment = Comment::query()->with('post')->findOrFail($commentId);

        if (! $comment->post || ! $this->posts->canView($user, $comment->post)) {
            throw (new ModelNotFoundException())->setModel(Comment::class);
        }

        return $comment;
    }
}
