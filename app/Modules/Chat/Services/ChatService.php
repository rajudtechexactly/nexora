<?php

declare(strict_types=1);

namespace App\Modules\Chat\Services;

use App\Modules\Chat\Events\MessageSent;
use App\Modules\Chat\Models\Conversation;
use App\Modules\Chat\Models\Message;
use App\Modules\Chat\Repositories\Contracts\ConversationRepositoryInterface;
use App\Modules\Chat\Repositories\Contracts\MessageRepositoryInterface;
use App\Modules\Friendship\Repositories\Contracts\FriendshipRepositoryInterface;
use App\Modules\Shared\Services\BaseService;
use App\Modules\Shared\Services\MediaService;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class ChatService extends BaseService
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversations,
        private readonly MessageRepositoryInterface $messages,
        private readonly FriendshipRepositoryInterface $friendships,
        private readonly UserRepositoryInterface $users,
        private readonly MediaService $media,
    ) {
    }

    /** Open (or reuse) a 1:1 conversation with another user. */
    public function startDirect(User $actor, int $otherUserId): Conversation
    {
        if ($actor->id === $otherUserId) {
            $this->reject('You cannot message yourself.');
        }

        $this->users->findOrFail($otherUserId);

        if (in_array($this->friendships->statusBetween($actor->id, $otherUserId), ['blocked', 'blocked_by_them'], true)) {
            $this->reject('You cannot message this user.');
        }

        $existing = $this->conversations->findDirectBetween($actor->id, $otherUserId);
        if ($existing) {
            return $existing;
        }

        return $this->transaction(function () use ($actor, $otherUserId): Conversation {
            /** @var Conversation $conversation */
            $conversation = $this->conversations->create([
                'type'       => Conversation::TYPE_DIRECT,
                'created_by' => $actor->id,
            ]);

            $conversation->participants()->attach([$actor->id, $otherUserId]);

            return $conversation->load('participants.profile');
        });
    }

    public function listConversations(User $actor, int $perPage = 20): LengthAwarePaginator
    {
        return $this->conversations->forUser($actor->id, $perPage);
    }

    public function listMessages(User $actor, int $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        $conversation = $this->authorizeParticipant($actor, $conversationId);
        $this->touchRead($conversation, $actor->id);

        return $this->messages->forConversation($conversationId, $perPage);
    }

    public function sendMessage(User $actor, int $conversationId, ?string $body, ?UploadedFile $attachment = null): Message
    {
        $conversation = $this->authorizeParticipant($actor, $conversationId);

        $body = $body !== null ? trim($body) : null;

        if (($body === null || $body === '') && ! $attachment) {
            $this->reject('A message needs text or an attachment.');
        }

        $message = $this->transaction(function () use ($actor, $conversation, $body, $attachment): Message {
            $type = Message::TYPE_TEXT;
            $path = null;
            $mime = null;

            if ($attachment) {
                $isImage = str_starts_with((string) $attachment->getMimeType(), 'image/');
                $stored = $isImage
                    ? $this->media->storeImage($attachment, "chat/{$conversation->id}")
                    : $this->media->storeFile($attachment, "chat/{$conversation->id}");
                $type = $isImage ? Message::TYPE_IMAGE : Message::TYPE_FILE;
                $path = $stored['path'];
                $mime = $stored['mime'];
            }

            /** @var Message $message */
            $message = $conversation->messages()->create([
                'user_id'         => $actor->id,
                'type'            => $type,
                'body'            => $body !== '' ? $body : null,
                'attachment_path' => $path,
                'attachment_mime' => $mime,
            ]);

            $conversation->forceFill(['last_message_at' => now()])->save();
            // The sender has implicitly read up to their own message.
            $conversation->participants()->updateExistingPivot($actor->id, ['last_read_at' => now()]);

            return $message;
        });

        broadcast(new MessageSent($message->load('sender.profile')));

        return $message;
    }

    public function markRead(User $actor, int $conversationId): void
    {
        $conversation = $this->authorizeParticipant($actor, $conversationId);
        $this->touchRead($conversation, $actor->id);
    }

    private function authorizeParticipant(User $actor, int $conversationId): Conversation
    {
        $conversation = $this->conversations->findForUser($conversationId, $actor->id);

        if (! $conversation) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(Conversation::class);
        }

        return $conversation;
    }

    private function touchRead(Conversation $conversation, int $userId): void
    {
        $conversation->participants()->updateExistingPivot($userId, ['last_read_at' => now()]);
    }

    /** @throws ValidationException */
    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['chat' => [$message]]);
    }
}
