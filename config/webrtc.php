<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| WebRTC ICE Configuration
|--------------------------------------------------------------------------
|
| Returned to clients before they establish a peer connection. STUN handles
| most NAT scenarios; a TURN relay is required for symmetric NATs and should
| be configured in production via the WEBRTC_TURN_* environment variables.
|
*/

return [
    'ice_servers' => array_values(array_filter([
        [
            'urls' => array_filter(explode(',', (string) env('WEBRTC_STUN_URLS', 'stun:stun.l.google.com:19302'))),
        ],
        env('WEBRTC_TURN_URLS') ? [
            'urls'       => array_filter(explode(',', (string) env('WEBRTC_TURN_URLS'))),
            'username'   => env('WEBRTC_TURN_USERNAME'),
            'credential' => env('WEBRTC_TURN_CREDENTIAL'),
        ] : null,
    ])),

    // Seconds a ringing call waits for an answer before auto-cancelling.
    'ring_timeout' => (int) env('WEBRTC_RING_TIMEOUT', 45),
];
