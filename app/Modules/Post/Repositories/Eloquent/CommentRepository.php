<?php

declare(strict_types=1);

namespace App\Modules\Post\Repositories\Eloquent;

use App\Modules\Post\Models\Comment;
use App\Modules\Post\Repositories\Contracts\CommentRepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
    protected function model(): string
    {
        return Comment::class;
    }

    public function forPost(int $postId, int $viewerId, int $perPage = 20): LengthAwarePaginator
    {
        $reaction = fn ($q) => $q->where('user_id', $viewerId);

        return $this->query()
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->with([
                'user.profile',
                'viewerReaction' => $reaction,
                'replies' => fn ($q) => $q->with(['user.profile', 'viewerReaction' => $reaction])->oldest(),
            ])
            ->latest()
            ->paginate($perPage);
    }
}
