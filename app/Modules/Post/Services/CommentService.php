<?php

declare(strict_types=1);

namespace App\Modules\Post\Services;

use App\Modules\Post\Events\PostCommented;
use App\Modules\Post\Events\PostStatsUpdated;
use App\Modules\Post\Models\Comment;
use App\Modules\Post\Models\Post;
use App\Modules\Post\Models\Reaction;
use App\Modules\Post\Repositories\Contracts\CommentRepositoryInterface;
use App\Modules\Shared\Services\BaseService;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CommentService extends BaseService
{
    public function __construct(private readonly CommentRepositoryInterface $comments)
    {
    }

    public function list(int $postId, int $viewerId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->comments->forPost($postId, $viewerId, $perPage);
    }

    public function create(User $actor, Post $post, string $content, ?int $parentId = null): Comment
    {
        if ($parentId !== null) {
            $parent = Comment::query()->where('id', $parentId)->where('post_id', $post->id)->first();

            if (! $parent) {
                throw ValidationException::withMessages(['parent_id' => ['The comment being replied to was not found.']]);
            }
            // Only one level of nesting — replies attach to the top-level comment.
            $parentId = $parent->parent_id ?? $parent->id;
        }

        $comment = $this->transaction(function () use ($actor, $post, $content, $parentId): Comment {
            /** @var Comment $comment */
            $comment = $post->comments()->create([
                'user_id'   => $actor->id,
                'parent_id' => $parentId,
                'content'   => $content,
            ]);

            $post->increment('comments_count');

            return $comment;
        });

        event(new PostCommented($comment));

        $post->refresh();
        event(new PostStatsUpdated($post->id, (int) $post->reactions_count, (int) $post->comments_count));

        return $comment->load('user.profile');
    }

    public function delete(User $actor, int $commentId): void
    {
        /** @var Comment|null $comment */
        $comment = Comment::query()->with('post:id,user_id,comments_count')->find($commentId);

        if (! $comment) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(Comment::class);
        }

        // The comment's author OR the post's owner may delete it.
        if ($comment->user_id !== $actor->id && $comment->post?->user_id !== $actor->id) {
            throw ValidationException::withMessages(['comment' => ['You cannot delete this comment.']]);
        }

        $this->transaction(function () use ($comment): void {
            // Replies cascade at the DB level; account for them in the counter
            // and clear the polymorphic reactions (no DB FK) for this comment
            // and its replies first.
            $replyIds = $comment->replies()->pluck('id')->all();
            Reaction::query()
                ->where('reactable_type', $comment->getMorphClass())
                ->whereIn('reactable_id', array_merge([$comment->id], $replyIds))
                ->delete();

            $removed = 1 + count($replyIds);
            $comment->delete();
            $comment->post?->decrement('comments_count', $removed);
        });

        if ($comment->post) {
            $comment->post->refresh();
            event(new PostStatsUpdated($comment->post->id, (int) $comment->post->reactions_count, (int) $comment->post->comments_count));
        }
    }
}
