<?php

declare(strict_types=1);

namespace App\Modules\Friendship\Http\Controllers;

use App\Modules\Friendship\Http\Resources\FriendRequestResource;
use App\Modules\Friendship\Services\FriendshipService;
use App\Modules\Shared\Http\Controllers\ApiController;
use App\Modules\Friendship\Repositories\Contracts\FriendshipRepositoryInterface;
use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendshipController extends ApiController
{
    public function __construct(
        private readonly FriendshipService $service,
        private readonly FriendshipRepositoryInterface $friendships,
    ) {
    }

    public function friends(Request $request): JsonResponse
    {
        $friends = $this->friendships->friends($request->user()->id, (int) $request->integer('per_page', 20));

        return $this->ok(UserResource::collection($friends));
    }

    public function incoming(Request $request): JsonResponse
    {
        $requests = $this->friendships->incomingRequests($request->user()->id, (int) $request->integer('per_page', 20));

        return $this->ok(FriendRequestResource::collection($requests));
    }

    public function outgoing(Request $request): JsonResponse
    {
        $requests = $this->friendships->outgoingRequests($request->user()->id, (int) $request->integer('per_page', 20));

        return $this->ok(FriendRequestResource::collection($requests));
    }

    public function blocked(Request $request): JsonResponse
    {
        $blocked = $this->friendships->blocked($request->user()->id, (int) $request->integer('per_page', 20));

        return $this->ok(FriendRequestResource::collection($blocked));
    }

    public function suggestions(Request $request): JsonResponse
    {
        $suggestions = $this->friendships->suggestions($request->user()->id, (int) $request->integer('limit', 20));

        return $this->ok(UserResource::collection($suggestions));
    }

    public function sendRequest(Request $request, int $user): JsonResponse
    {
        $friendship = $this->service->sendRequest($request->user(), $user);

        return $this->created(['status' => $friendship->status], 'Friend request sent.');
    }

    public function accept(Request $request, int $user): JsonResponse
    {
        $this->service->accept($request->user(), $user);

        return $this->ok(message: 'Friend request accepted.');
    }

    public function decline(Request $request, int $user): JsonResponse
    {
        $this->service->decline($request->user(), $user);

        return $this->ok(message: 'Friend request declined.');
    }

    public function cancel(Request $request, int $user): JsonResponse
    {
        $this->service->cancel($request->user(), $user);

        return $this->ok(message: 'Friend request cancelled.');
    }

    public function unfriend(Request $request, int $user): JsonResponse
    {
        $this->service->unfriend($request->user(), $user);

        return $this->ok(message: 'Unfriended successfully.');
    }

    public function block(Request $request, int $user): JsonResponse
    {
        $this->service->block($request->user(), $user);

        return $this->ok(message: 'User blocked.');
    }

    public function unblock(Request $request, int $user): JsonResponse
    {
        $this->service->unblock($request->user(), $user);

        return $this->ok(message: 'User unblocked.');
    }
}
