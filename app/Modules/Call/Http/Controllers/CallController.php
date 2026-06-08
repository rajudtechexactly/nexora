<?php

declare(strict_types=1);

namespace App\Modules\Call\Http\Controllers;

use App\Modules\Call\Http\Requests\AnswerCallRequest;
use App\Modules\Call\Http\Requests\CandidateRequest;
use App\Modules\Call\Http\Requests\InitiateCallRequest;
use App\Modules\Call\Http\Resources\CallResource;
use App\Modules\Call\Services\CallService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallController extends ApiController
{
    public function __construct(private readonly CallService $calls)
    {
    }

    /** ICE server config the client needs before opening a peer connection. */
    public function iceServers(): JsonResponse
    {
        return $this->ok([
            'ice_servers'  => config('webrtc.ice_servers'),
            'ring_timeout' => config('webrtc.ring_timeout'),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $history = $this->calls->history($request->user(), (int) $request->integer('per_page', 20));

        return $this->ok(CallResource::collection($history));
    }

    public function initiate(InitiateCallRequest $request): JsonResponse
    {
        $call = $this->calls->initiate(
            $request->user(),
            (int) $request->integer('callee_id'),
            (string) $request->string('type'),
            (array) $request->input('sdp'),
        );

        return $this->created(new CallResource($call->load('caller.profile', 'callee.profile')), 'Calling…');
    }

    public function answer(AnswerCallRequest $request, int $call): JsonResponse
    {
        $this->calls->answer($request->user(), $call, (array) $request->input('sdp'));

        return $this->ok(message: 'Call answered.');
    }

    public function candidate(CandidateRequest $request, int $call): JsonResponse
    {
        $this->calls->candidate($request->user(), $call, (array) $request->input('candidate'));

        return $this->ok(message: 'Candidate relayed.');
    }

    public function decline(Request $request, int $call): JsonResponse
    {
        $this->calls->decline($request->user(), $call);

        return $this->ok(message: 'Call declined.');
    }

    public function hangup(Request $request, int $call): JsonResponse
    {
        $this->calls->hangup($request->user(), $call);

        return $this->ok(message: 'Call ended.');
    }
}
