<?php

namespace Rennokki\Chargeswarm\Events;

use Illuminate\Queue\SerializesModels;

class QuoteUpdated
{
    use SerializesModels;

    public $payload;
    public $storedSubscription;
    public $subscription;
    public $plan;

    /**
     * @param $payload The payload, in JSON, from the webhook.
     * @param $storedSubscription The subscription stored in the DB, if any.
     * @param $subscription The subscription came from the webhook (same as $payload->content->subscription)
     * @param $plan The plan of the subscription, if any.
     * @return void
     */
    public function __construct($payload, $storedSubscription, $subscription, $plan)
    {
        $this->payload = $payload;
        $this->storedSubscription = $storedSubscription;
        $this->subscription = $subscription;
        $this->plan = $plan;
    }
}
