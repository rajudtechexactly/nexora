<?php

declare(strict_types=1);

namespace App\Modules\Post\Repositories\Contracts;

use App\Modules\Shared\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommentRepositoryInterface extends RepositoryInterface
{
    /**
     * Top-level comments for a post (newest last is typical, but we page newest
     * first), with author, replies and the viewer's reaction loaded.
     */
    public function forPost(int $postId, int $viewerId, int $perPage = 20): LengthAwarePaginator;
}
