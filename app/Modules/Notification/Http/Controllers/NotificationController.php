<?php

declare(strict_types=1);

namespace App\Modules\Notification\Http\Controllers;

use App\Modules\Notification\Http\Resources\NotificationResource;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $list = $this->notifications->list($request->user()->id, (int) $request->integer('per_page', 20));

        return $this->ok(NotificationResource::collection($list));
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->ok(['unread' => $this->notifications->unreadCount($request->user()->id)]);
    }

    public function read(Request $request, int $notification): JsonResponse
    {
        $this->notifications->markRead($request->user(), $notification);

        return $this->ok(message: 'Marked as read.');
    }

    public function readAll(Request $request): JsonResponse
    {
        $this->notifications->markAllRead($request->user());

        return $this->ok(message: 'All notifications marked as read.');
    }
}
