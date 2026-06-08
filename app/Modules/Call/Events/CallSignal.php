<?php

declare(strict_types=1);

namespace App\Modules\Call\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Relays a single WebRTC signaling message to the recipient's private channel.
 *
 * Broadcast NOW (synchronous) rather than queued — call signaling (offer,
 * answer, ICE candidates, hang-up) is latency-sensitive and must not wait for
 * a queue worker.
 *
 * signal ∈ incoming | answer | candidate | declined | ended | canceled
 */
class CallSignal implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $recipientId,
        public string $signal,
        public array $payload = [],
    ) {
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->recipientId)];
    }

    public function broadcastAs(): string
    {
        return 'call.signal';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['signal' => $this->signal] + $this->payload;
    }
}
