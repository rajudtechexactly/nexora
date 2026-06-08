<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full profile view: identity + extended profile + (optionally) relationship
 * context such as friendship status when viewing another user.
 *
 * @mixin \App\Modules\User\Models\User
 */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'username'          => $this->username,
            'email'             => $this->when($this->id === optional($request->user())->id, $this->email),
            'phone'             => $this->when($this->id === optional($request->user())->id, $this->phone),
            'date_of_birth'     => $this->date_of_birth?->toDateString(),
            'gender'            => $this->gender,
            'email_verified'    => ! is_null($this->email_verified_at),
            'last_active_at'    => $this->last_active_at?->toIso8601String(),
            'created_at'        => $this->created_at?->toIso8601String(),
            'profile'           => [
                'bio'                 => $this->profile?->bio,
                'avatar_url'          => $this->profile?->avatar_url,
                'cover_url'           => $this->profile?->cover_url,
                'location'            => $this->profile?->location,
                'website'             => $this->profile?->website,
                'work'                => $this->profile?->work,
                'education'           => $this->profile?->education,
                'relationship_status' => $this->profile?->relationship_status,
                'visibility'          => $this->profile?->visibility,
            ],
            // Counts and friendship context are merged in by the controller via additional().
            'stats'             => $this->when(isset($this->posts_count), fn () => [
                'posts'   => $this->posts_count ?? 0,
                'friends' => $this->friends_count ?? 0,
            ]),
            'friendship_status' => $this->when(isset($this->friendship_status), fn () => $this->friendship_status),
        ];
    }
}
