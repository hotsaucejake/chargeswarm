<?php

namespace Rennokki\Chargeswarm\Traits;

use Rennokki\Chargeswarm\SubscriptionBuilder;

trait Billable
{
    public function subscriptions()
    {
        return $this->morphMany(config('chargeswarm.models.subscription'), 'model');
    }

    /**
     * Start building a new subscription.
     *
     * @param string $planId
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function subscription(string $planId)
    {
        return new SubscriptionBuilder($this, $planId);
    }

    /**
     * Get the active subscriptions of this model.
     *
     * @return \Illuminate\Support\Collection
     */
    public function activeSubscriptions()
    {
        $this->load(['subscriptions']);

        $activeSubscriptions = $this->subscriptions->filter(function ($item, $key) {
            return $item->active();
        });

        return $activeSubscriptions;
    }

    /**
     * Check if the model is subscribed to a plan.
     *
     * @param string $planId
     * @return bool
     */
    public function subscribed(string $planId)
    {
        $activeSubscriptions = $this->activeSubscriptions()->where('plan_id', $planId);

        return (bool) ($this->activeSubscriptions()->where('plan_id', $planId)
            ->filter(function ($item, $index) {
                return $item->active();
            })->count() > 0);
    }
}