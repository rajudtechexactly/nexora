<?php

declare(strict_types=1);

namespace App\Modules\Chat\Http\Controllers;

use App\Modules\Chat\Http\Requests\SendMessageRequest;
use App\Modules\Chat\Http\Requests\StartConversationRequest;
use App\Modules\Chat\Http\Resources\ConversationResource;
use App\Modules\Chat\Http\Resources\MessageResource;
use App\Modules\Chat\Services\ChatService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends ApiController
{
    public function __construct(private readonly ChatService $chat)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->chat->listConversations($request->user(), (int) $request->integer('per_page', 20));

        return $this->ok(ConversationResource::collection($conversations));
    }

    public function store(StartConversationRequest $request): JsonResponse
    {
        $conversation = $this->chat->startDirect($request->user(), (int) $request->integer('user_id'));

        return $this->ok(new ConversationResource($conversation->loadMissing('participants.profile', 'latestMessage.sender.profile')), 'Conversation ready.');
    }

    public function messages(Request $request, int $conversation): JsonResponse
    {
        $messages = $this->chat->listMessages($request->user(), $conversation, (int) $request->integer('per_page', 30));

        return $this->ok(MessageResource::collection($messages));
    }

    public function send(SendMessageRequest $request, int $conversation): JsonResponse
    {
        $message = $this->chat->sendMessage(
            $request->user(),
            $conversation,
            $request->input('body'),
            $request->file('attachment'),
        );

        return $this->created(new MessageResource($message->loadMissing('sender.profile')), 'Message sent.');
    }

    public function read(Request $request, int $conversation): JsonResponse
    {
        $this->chat->markRead($request->user(), $conversation);

        return $this->ok(message: 'Marked as read.');
    }
}
