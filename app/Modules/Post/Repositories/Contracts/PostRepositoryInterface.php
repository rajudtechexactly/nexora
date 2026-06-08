<?php

declare(strict_types=1);

namespace App\Modules\Post\Repositories\Contracts;

use App\Modules\Post\Models\Post;
use App\Modules\Shared\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface extends RepositoryInterface
{
    /**
     * The home feed: posts authored by $authorIds (the viewer + their friends),
     * hiding friends' private posts. Eager-loads author, media and the viewer's
     * own reaction.
     *
     * @param  array<int>  $authorIds
     */
    public function feed(array $authorIds, int $viewerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Posts by a single author, filtered to the visibilities the viewer may see.
     *
     * @param  array<string>  $visibilities
     */
    public function byAuthor(int $authorId, int $viewerId, array $visibilities, int $perPage = 15): LengthAwarePaginator;

    /** A single post with author, media and the viewer's reaction loaded. */
    public function findForViewer(int $postId, int $viewerId): ?Post;
}
