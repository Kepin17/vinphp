<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Throttle;
use App\Models\Subscriber;

class SubscribeController
{
    public function store(): void
    {
        if (!Throttle::attempt('subscribe', maxAttempts: 5, decaySeconds: 60)) {
            http_response_code(429);
            redirect('/?subscribed=throttled');
        }

        $email = trim((string) Request::post('email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirect('/?subscribed=0');
        }

        Subscriber::create(['email' => $email]);
        redirect('/?subscribed=1');
    }
}
