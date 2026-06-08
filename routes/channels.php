<?php

use App\Modules\Chat\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

// Per-user private channel (notifications, presence).
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// A user may listen on their own private channel (notifications, incoming calls).
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Conversation channel: only participants may subscribe.
Broadcast::channel('conversation.{conversation}', function ($user, int $conversation) {
    return Conversation::query()
        ->where('id', $conversation)
        ->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
        ->exists();
});
