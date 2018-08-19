<?php

namespace Rennokki\Chargeswarm\Traits;

use Rennokki\Chargeswarm\SubscriptionBuilder;

trait Billable
{
    public function subscriptions()
    {
        return $this->morphMany(config('chargeswarm.models.subscription'), 'model');
    }

    public function invoices()
    {
        return $this->morphMany(config('chargeswarm.models.invoice'), 'model');
    }

    /**
     * Get the the invoice, if any.
     *
     * @param string $invoiceId
     * @return null|Chargebee_Invoice
     */
    public function retrieveInvoice(string $invoiceId)
    {
        $invoice = $this->invoices()->find($invoiceId);

        if (! $invoice) {
            return;
        }

        return $invoice->retrieve();
    }

    /**
     * Get the download link of the invoice, if any.
     *
     * @param string $invoiceId
     * @return null|Chargebee_Download
     */
    public function downloadLinkForInvoice(string $invoiceId)
    {
        $invoice = $this->invoices()->find($invoiceId);

        if (! $invoice) {
            return;
        }

        return $invoice->downloadLink();
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
