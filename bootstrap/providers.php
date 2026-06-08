<?php

use App\Modules\Notification\Providers\NotificationEventServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\BroadcastServiceProvider;
use App\Providers\DomainServiceProvider;
use App\Providers\NativeAppServiceProvider;

return [
    // Must run first: switches to offline-safe drivers when on a device,
    // before session/cache middleware try to reach the database.
    NativeAppServiceProvider::class,
    AppServiceProvider::class,
    DomainServiceProvider::class,
    BroadcastServiceProvider::class,
    // Translates domain events (friend requests, comments, reactions, messages)
    // into in-app notifications.
    NotificationEventServiceProvider::class,
];
