<?php

declare(strict_types=1);

namespace App\Modules\Post\Http\Resources;

use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Post\Models\Comment
 */
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewerId = optional($request->user())->id;

        return [
            'id'              => $this->id,
            'post_id'         => $this->post_id,
            'parent_id'       => $this->parent_id,
            'content'         => $this->content,
            'author'          => new UserResource($this->whenLoaded('user')),
            'reactions_count' => (int) $this->reactions_count,
            'my_reaction'     => $this->whenLoaded('viewerReaction', fn () => optional($this->viewerReaction->first())->type),
            'replies'         => CommentResource::collection($this->whenLoaded('replies')),
            'can_delete'      => $this->user_id === $viewerId,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
