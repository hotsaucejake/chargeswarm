<?php

namespace Rennokki\Chargeswarm\Events;

use Illuminate\Queue\SerializesModels;

class SubscriptionActivated
{
    use SerializesModels;

    public $payload;
    public $storedSubscription;
    public $subscription;

    /**
     * @param $payload The payload, in JSON, from the webhook.
     * @param $storedSubscription The subscription stored in the DB, if any.
     * @param $subscription The subscription came from the webhook (same as $payload->content->subscription)
     * @return void
     */
    public function __construct($payload, $storedSubscription, $subscription)
    {
        $this->payload = $payload;
        $this->storedSubscription = $storedSubscription;
        $this->subscription = $subscription;
    }
}
