<?php

declare(strict_types=1);

namespace App\Modules\User\Services;

use App\Modules\Friendship\Repositories\Contracts\FriendshipRepositoryInterface;
use App\Modules\Shared\Services\BaseService;
use App\Modules\Shared\Services\MediaService;
use App\Modules\User\Models\Profile;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

class UserService extends BaseService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly FriendshipRepositoryInterface $friendships,
        private readonly MediaService $media,
    ) {
    }

    /**
     * Resolve a user for profile display, attaching counts and the viewer's
     * friendship status with them.
     */
    public function viewByUsername(string $username, User $viewer): User
    {
        $user = $this->users->findByUsername($username);

        if (! $user) {
            throw new ModelNotFoundException('User not found.');
        }

        return $this->decorateForViewer($user, $viewer);
    }

    public function viewById(int $id, User $viewer): User
    {
        /** @var User $user */
        $user = $this->users->findOrFail($id);

        return $this->decorateForViewer($user, $viewer);
    }

    /**
     * Update the authenticated user's identity + profile fields.
     */
    public function updateProfile(User $user, array $data): User
    {
        return $this->transaction(function () use ($user, $data): User {
            $userFields = array_filter([
                'name'          => $data['name'] ?? null,
                'phone'         => $data['phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender'        => $data['gender'] ?? null,
            ], fn ($v) => $v !== null);

            if ($userFields !== []) {
                $user->fill($userFields)->save();
            }

            $profileFields = array_intersect_key($data, array_flip([
                'bio', 'location', 'website', 'work', 'education', 'relationship_status', 'visibility',
            ]));

            $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
            $profile->fill($profileFields);
            $user->profile()->save($profile);

            return $user->load('profile');
        });
    }

    public function updateAvatar(User $user, UploadedFile $file): User
    {
        $profile = $user->profile ?? $user->profile()->create(['visibility' => 'public']);
        $old = $profile->avatar_path;

        $stored = $this->media->storeImage($file, "avatars/{$user->id}", maxWidth: 512, thumbnail: false);
        $profile->forceFill(['avatar_path' => $stored['path']])->save();

        $this->media->delete($old);

        return $user->load('profile');
    }

    public function updateCover(User $user, UploadedFile $file): User
    {
        $profile = $user->profile ?? $user->profile()->create(['visibility' => 'public']);
        $old = $profile->cover_path;

        $stored = $this->media->storeImage($file, "covers/{$user->id}", maxWidth: 1600, thumbnail: false);
        $profile->forceFill(['cover_path' => $stored['path']])->save();

        $this->media->delete($old);

        return $user->load('profile');
    }

    public function search(string $term, User $viewer, int $perPage = 20): LengthAwarePaginator
    {
        return $this->users->search($term, $viewer->id, $perPage);
    }

    public function deactivate(User $user): void
    {
        $user->forceFill(['is_active' => false])->save();
    }

    /**
     * Attach computed counts + the viewer's friendship status to a user.
     */
    private function decorateForViewer(User $user, User $viewer): User
    {
        $user->loadMissing('profile');

        // Counts: friends from the friendship graph; posts only once that module exists.
        $user->setAttribute('friends_count', $this->friendships->friendIds($user->id)->count());
        $user->setAttribute('posts_count', class_exists(\App\Modules\Post\Models\Post::class)
            ? $user->posts()->count()
            : 0);

        $user->setAttribute(
            'friendship_status',
            $user->id === $viewer->id
                ? 'self'
                : $this->friendships->statusBetween($viewer->id, $user->id),
        );

        return $user;
    }
}
