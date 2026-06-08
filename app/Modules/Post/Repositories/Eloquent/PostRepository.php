<?php

declare(strict_types=1);

namespace App\Modules\Post\Repositories\Eloquent;

use App\Modules\Post\Models\Post;
use App\Modules\Post\Repositories\Contracts\PostRepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    protected function model(): string
    {
        return Post::class;
    }

    public function feed(array $authorIds, int $viewerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->withViewer($viewerId)
            ->whereIn('user_id', $authorIds)
            ->where(function (Builder $q) use ($viewerId) {
                // Own posts: all. Others' posts: public or friends-only.
                $q->where('user_id', $viewerId)
                    ->orWhereIn('visibility', [Post::VISIBILITY_PUBLIC, Post::VISIBILITY_FRIENDS]);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function byAuthor(int $authorId, int $viewerId, array $visibilities, int $perPage = 15): LengthAwarePaginator
    {
        return $this->withViewer($viewerId)
            ->where('user_id', $authorId)
            ->whereIn('visibility', $visibilities)
            ->latest()
            ->paginate($perPage);
    }

    public function findForViewer(int $postId, int $viewerId): ?Post
    {
        /** @var Post|null $post */
        $post = $this->withViewer($viewerId)->find($postId);

        return $post;
    }

    /** Base query with the author, media and the viewer's own reaction eager-loaded. */
    private function withViewer(int $viewerId): Builder
    {
        return $this->query()->with([
            'user.profile',
            'media',
            'viewerReaction' => fn ($q) => $q->where('user_id', $viewerId),
        ]);
    }
}
