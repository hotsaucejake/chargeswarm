<?php

namespace Rennokki\Chargeswarm\Events;

use Illuminate\Queue\SerializesModels;

class WebhookReceived
{
    use SerializesModels;

    public $payload;

    /**
     * @param $payload The payload, in JSON, from the webhook.
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }
}
