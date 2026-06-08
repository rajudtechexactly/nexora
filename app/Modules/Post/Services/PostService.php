<?php

declare(strict_types=1);

namespace App\Modules\Post\Services;

use App\Modules\Friendship\Repositories\Contracts\FriendshipRepositoryInterface;
use App\Modules\Post\Models\Comment;
use App\Modules\Post\Models\Post;
use App\Modules\Post\Models\PostMedia;
use App\Modules\Post\Models\Reaction;
use App\Modules\Post\Repositories\Contracts\PostRepositoryInterface;
use App\Modules\Shared\Services\BaseService;
use App\Modules\Shared\Services\MediaService;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class PostService extends BaseService
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly FriendshipRepositoryInterface $friendships,
        private readonly MediaService $media,
    ) {
    }

    /**
     * Create a post with optional image/video attachments.
     *
     * @param  array<UploadedFile>  $files
     */
    public function create(User $author, array $data, array $files = []): Post
    {
        $content = trim((string) ($data['content'] ?? ''));

        if ($content === '' && $files === []) {
            $this->reject('A post needs text or at least one photo.');
        }

        $post = $this->transaction(function () use ($author, $data, $content, $files): Post {
            /** @var Post $post */
            $post = $this->posts->create([
                'user_id'    => $author->id,
                'content'    => $content !== '' ? $content : null,
                'visibility' => $data['visibility'] ?? Post::VISIBILITY_FRIENDS,
            ]);

            foreach (array_values($files) as $position => $file) {
                $this->attachMedia($post, $file, $position);
            }

            return $post;
        });

        return $this->posts->findForViewer($post->id, $author->id) ?? $post->load(['user.profile', 'media']);
    }

    public function update(User $actor, int $postId, array $data): Post
    {
        $post = $this->posts->findForViewer($postId, $actor->id);

        if (! $post) {
            $this->notFound();
        }
        $this->ensureOwner($actor, $post);

        $post->fill(array_filter([
            'content'    => $data['content'] ?? null,
            'visibility' => $data['visibility'] ?? null,
        ], fn ($v) => $v !== null))->save();

        return $post->fresh(['user.profile', 'media', 'viewerReaction']);
    }

    public function delete(User $actor, int $postId): void
    {
        /** @var Post|null $post */
        $post = $this->posts->find($postId);

        if (! $post) {
            $this->notFound();
        }
        $this->ensureOwner($actor, $post);

        $this->transaction(function () use ($post): void {
            // Reactions are polymorphic (no DB FK), so clear the post's and its
            // comments' reactions explicitly. Media files + comment rows are
            // removed by FK cascade when the post is deleted.
            $commentIds = $post->comments()->pluck('id')->all();
            Reaction::query()
                ->where(fn ($q) => $q->where('reactable_type', $post->getMorphClass())->where('reactable_id', $post->id))
                ->when($commentIds !== [], fn ($q) => $q->orWhere(fn ($w) => $w
                    ->where('reactable_type', (new Comment())->getMorphClass())
                    ->whereIn('reactable_id', $commentIds)))
                ->delete();

            $paths = $post->media()->get()->flatMap(fn (PostMedia $m) => [$m->path, $m->thumb_path])->all();
            $this->media->delete(...$paths);
            $post->delete(); // cascades media + comments via FK
        });
    }

    public function feed(User $viewer, int $perPage = 15): LengthAwarePaginator
    {
        $friendIds = $this->friendships->friendIds($viewer->id)->all();
        $authorIds = array_values(array_unique([...$friendIds, $viewer->id]));

        return $this->posts->feed($authorIds, $viewer->id, $perPage);
    }

    /** Posts on a user's profile, filtered to what the viewer is allowed to see. */
    public function byAuthor(User $viewer, int $authorId, int $perPage = 15): LengthAwarePaginator
    {
        $status = $this->friendships->statusBetween($viewer->id, $authorId);

        if (in_array($status, ['blocked', 'blocked_by_them'], true)) {
            $this->reject('You cannot view this user\'s posts.');
        }

        $visibilities = match (true) {
            $viewer->id === $authorId => Post::VISIBILITIES,
            $status === 'friends'     => [Post::VISIBILITY_PUBLIC, Post::VISIBILITY_FRIENDS],
            default                   => [Post::VISIBILITY_PUBLIC],
        };

        return $this->posts->byAuthor($authorId, $viewer->id, $visibilities, $perPage);
    }

    public function show(User $viewer, int $postId): Post
    {
        $post = $this->posts->findForViewer($postId, $viewer->id);

        if (! $post || ! $this->canView($viewer, $post)) {
            $this->notFound();
        }

        return $post;
    }

    /** Whether $viewer is allowed to see $post given its visibility + relationship. */
    public function canView(User $viewer, Post $post): bool
    {
        if ($post->user_id === $viewer->id || $post->visibility === Post::VISIBILITY_PUBLIC) {
            return true;
        }

        if ($post->visibility === Post::VISIBILITY_PRIVATE) {
            return false;
        }

        return $this->friendships->areFriends($viewer->id, $post->user_id);
    }

    private function attachMedia(Post $post, UploadedFile $file, int $position): void
    {
        $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');

        if ($isVideo) {
            $stored = $this->media->storeFile($file, "posts/{$post->id}");
            $post->media()->create([
                'type'     => PostMedia::TYPE_VIDEO,
                'path'     => $stored['path'],
                'size'     => $stored['size'],
                'mime'     => $stored['mime'],
                'position' => $position,
            ]);

            return;
        }

        $stored = $this->media->storeImage($file, "posts/{$post->id}", thumbnail: true);
        $post->media()->create([
            'type'       => PostMedia::TYPE_IMAGE,
            'path'       => $stored['path'],
            'thumb_path' => $stored['thumb_path'],
            'width'      => $stored['width'],
            'height'     => $stored['height'],
            'size'       => $stored['size'],
            'mime'       => $stored['mime'],
            'position'   => $position,
        ]);
    }

    private function ensureOwner(User $actor, Post $post): void
    {
        if ($post->user_id !== $actor->id) {
            throw ValidationException::withMessages(['post' => ['You can only modify your own posts.']]);
        }
    }

    /** @throws ValidationException */
    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['post' => [$message]]);
    }

    /** @throws \Illuminate\Database\Eloquent\ModelNotFoundException */
    private function notFound(): never
    {
        throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(Post::class);
    }
}
