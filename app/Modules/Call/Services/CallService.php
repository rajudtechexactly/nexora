<?php

declare(strict_types=1);

namespace App\Modules\Call\Services;

use App\Modules\Call\Events\CallSignal;
use App\Modules\Call\Models\Call;
use App\Modules\Call\Repositories\Contracts\CallRepositoryInterface;
use App\Modules\Friendship\Repositories\Contracts\FriendshipRepositoryInterface;
use App\Modules\Shared\Services\BaseService;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrates 1:1 call lifecycle and relays WebRTC signaling between the two
 * peers. Media never touches the server — only SDP/ICE messages are relayed.
 */
class CallService extends BaseService
{
    public function __construct(
        private readonly CallRepositoryInterface $calls,
        private readonly FriendshipRepositoryInterface $friendships,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /** Caller starts a call and sends their SDP offer to the callee. */
    public function initiate(User $caller, int $calleeId, string $type, array $sdp): Call
    {
        if ($caller->id === $calleeId) {
            $this->reject('You cannot call yourself.');
        }

        $callee = $this->users->findOrFail($calleeId);

        if (in_array($this->friendships->statusBetween($caller->id, $calleeId), ['blocked', 'blocked_by_them'], true)) {
            $this->reject('You cannot call this user.');
        }

        /** @var Call $call */
        $call = $this->calls->create([
            'caller_id' => $caller->id,
            'callee_id' => $calleeId,
            'type'      => $type,
            'status'    => Call::STATUS_RINGING,
        ]);

        $caller->loadMissing('profile');

        event(new CallSignal($calleeId, 'incoming', [
            'call' => [
                'id'   => $call->id,
                'type' => $call->type,
            ],
            'from' => [
                'id'         => $caller->id,
                'name'       => $caller->name,
                'username'   => $caller->username,
                'avatar_url' => $caller->profile?->avatar_url,
            ],
            'sdp'  => $sdp,
        ]));

        return $call;
    }

    /** Callee accepts and returns their SDP answer to the caller. */
    public function answer(User $actor, int $callId, array $sdp): Call
    {
        $call = $this->authorizeCallee($actor, $callId);

        if ($call->status !== Call::STATUS_RINGING) {
            $this->reject('This call is no longer ringing.');
        }

        $call->forceFill(['status' => Call::STATUS_ONGOING, 'answered_at' => now()])->save();

        event(new CallSignal($call->caller_id, 'answer', ['call_id' => $call->id, 'sdp' => $sdp]));

        return $call;
    }

    /** Relay an ICE candidate to the other peer. */
    public function candidate(User $actor, int $callId, array $candidate): void
    {
        $call = $this->authorizeParticipant($actor, $callId);

        event(new CallSignal($call->otherParty($actor->id), 'candidate', [
            'call_id'   => $call->id,
            'candidate' => $candidate,
        ]));
    }

    /** Callee rejects a ringing call. */
    public function decline(User $actor, int $callId): void
    {
        $call = $this->authorizeCallee($actor, $callId);

        $call->forceFill(['status' => Call::STATUS_DECLINED, 'ended_at' => now()])->save();

        event(new CallSignal($call->caller_id, 'declined', ['call_id' => $call->id]));
    }

    /** Either party ends the call (or the caller cancels before it's answered). */
    public function hangup(User $actor, int $callId): void
    {
        $call = $this->authorizeParticipant($actor, $callId);

        if ($call->status === Call::STATUS_ENDED || $call->status === Call::STATUS_DECLINED) {
            return;
        }

        $wasRinging = $call->status === Call::STATUS_RINGING;
        $status = $wasRinging && $actor->id === $call->caller_id ? Call::STATUS_CANCELED : Call::STATUS_ENDED;

        $call->forceFill(['status' => $status, 'ended_at' => now()])->save();

        event(new CallSignal($call->otherParty($actor->id), $status === Call::STATUS_CANCELED ? 'canceled' : 'ended', [
            'call_id' => $call->id,
        ]));
    }

    public function history(User $actor, int $perPage = 20): LengthAwarePaginator
    {
        return $this->calls->history($actor->id, $perPage);
    }

    private function authorizeParticipant(User $actor, int $callId): Call
    {
        /** @var Call|null $call */
        $call = $this->calls->find($callId);

        if (! $call || ! $call->involves($actor->id)) {
            throw (new ModelNotFoundException())->setModel(Call::class);
        }

        return $call;
    }

    private function authorizeCallee(User $actor, int $callId): Call
    {
        $call = $this->authorizeParticipant($actor, $callId);

        if ($call->callee_id !== $actor->id) {
            $this->reject('Only the callee can perform this action.');
        }

        return $call;
    }

    /** @throws ValidationException */
    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['call' => [$message]]);
    }
}
