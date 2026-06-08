<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| One-Time Password (OTP) Settings
|--------------------------------------------------------------------------
|
| Email OTPs are used to verify a new account before its first login and to
| authorize a password reset. Codes are stored hashed and expire quickly.
|
*/

return [
    // Number of digits in the generated code.
    'length' => (int) env('OTP_LENGTH', 6),

    // Minutes a code stays valid after being issued.
    'ttl' => (int) env('OTP_TTL_MINUTES', 10),

    // Wrong guesses allowed before a code is burned and a new one is required.
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
];
