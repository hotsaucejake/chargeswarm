<?php

namespace Rennokki\Chargeswarm\Traits;

use Chargebee_Invoice as ChargebeeInvoice;
use Rennokki\Chargeswarm\SubscriptionBuilder;
use ChargeBee_Environment as ChargebeeEnvironment;

trait Billable
{
    public function subscriptions()
    {
        return $this->morphMany(config('chargeswarm.models.subscription'), 'model');
    }

    /**
     * Get all invoices associated with a subscription.
     *
     * @param string $subscriptionId
     * @param int $limit
     * @param string|null $nextOffset
     * @return null|Chargebee_ListResult
     */
    public function invoices(string $subscriptionId, int $limit = 20, $nextOffset = null)
    {
        if (! $this->subscriptions()->find($subscriptionId)) {
            return;
        }

        $subscription = $this->subscriptions()->find($subscriptionId);

        return $subscription->invoices($limit, $nextOffset);
    }

    /**
     * Get the invoice.
     *
     * @param string $invoiceId
     * @return Chargebee_Invoice
     */
    public function invoice(string $invoiceId)
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: config('chargeswarm.site'), (getenv('CHARGEBEE_KEY')) ?: config('chargeswarm.key'));

        $invoice = ChargebeeInvoice::retrieve($invoiceId);

        return $invoice->invoice();
    }

    /**
     * Get the download link of the invoice, if any.
     *
     * @param string $invoiceId
     * @return null|Chargebee_Download
     */
    public function downloadInvoice(string $invoiceId)
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: config('chargeswarm.site'), (getenv('CHARGEBEE_KEY')) ?: config('chargeswarm.key'));

        $invoice = ChargebeeInvoice::pdf($invoiceId);

        return $invoice->download();
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
