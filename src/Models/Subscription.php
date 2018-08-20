<?php

namespace Rennokki\Chargeswarm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Chargebee_Invoice as ChargebeeInvoice;
use ChargeBee_Environment as ChargebeeEnvironment;
use ChargeBee_Subscription as ChargebeeSubscription;

class Subscription extends Model
{
    protected $table = 'chargebee_subscriptions';
    protected $guarded = [];
    protected $dates = [
        'starts_at',
        'ends_at',
        'trial_starts_at',
        'trial_ends_at',
        'next_billing_at',
    ];
    public $incrementing = false;

    public function model()
    {
        return $this->morphTo();
    }

    public function usages()
    {
        return $this->hasMany(config('chargeswarm.models.subscriptionUsage'), 'subscription_id');
    }

    /**
     * Get all invoices associated with this subscription.
     *
     * @param int $limit
     * @param string|null $nextOffset
     * @return array
     */
    public function invoices(int $limit = 20, $nextOffset = null)
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        $invoices = ChargebeeInvoice::all([
            'subscriptionId[is]' => $this->id,
            'limit' => $limit,
            'next_offset' => $nextOffset,
        ]);

        $invoicesArray = [];

        foreach ($invoices as $invoice) {
            $invoicesArray[] = $invoice->invoice();
        }

        return $invoicesArray;
    }

    /**
     * Swap the current subscription to a new plan.
     *
     * @param string $planId
     * @param null|bool $endOfTerm
     * @return bool|\Rennokki\Chargeswarm\Models\Subscription
     */
    public function swap(string $planId, $endOfTerm = null)
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        if (! $this->active()) {
            return false;
        }

        $subscription = ChargebeeSubscription::update($this->id, [
            'planId' => $planId,
            'endOfTerm' => $endOfTerm,
        ])->subscription();

        $this->update([
            'plan_id' => $subscription->planId,
            'billing_period' => $subscription->billingPeriod,
            'billing_period_unit' => $subscription->billingPeriodUnit,
            'plan_quantity' => $subscription->planQuantity,
            'plan_free_quantity' => $subscription->planFreeQuantity,
            'starts_at' => $subscription->startedAt,
            'trial_starts_at' => $subscription->trialStart,
            'trial_ends_at' => $subscription->trialEnd,
            'next_billing_at' => $subscription->nextBillingAt,
            'status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Change the plan quantity.
     *
     * @param string $newQuantity
     * @param null|bool $endOfTerm
     * @return bool|\Rennokki\Chargeswarm\Models\Subscription
     */
    public function changeQuantity(int $newQuantity, $endOfTerm = null)
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        if (! $this->active()) {
            return false;
        }

        $subscription = ChargebeeSubscription::update($this->id, [
            'planQuantity' => $newQuantity,
            'endOfTerm' => $endofTerm,
        ])->subscription();

        $this->update([
            'plan_id' => $subscription->planId,
            'billing_period' => $subscription->billingPeriod,
            'billing_period_unit' => $subscription->billingPeriodUnit,
            'plan_quantity' => $subscription->planQuantity,
            'plan_free_quantity' => $subscription->planFreeQuantity,
            'starts_at' => $subscription->startedAt,
            'trial_starts_at' => $subscription->trialStart,
            'trial_ends_at' => $subscription->trialEnd,
            'next_billing_at' => $subscription->nextBillingAt,
            'status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Change the billing cycles for a subscription.
     *
     * @param int $billingCycles
     * @param null|bool $endOfTerm
     * @return bool|\Rennokki\Chargeswarm\Models\Subscription
     */
    public function updateBillingCycles($billingCycles, $endOfTerm = null)
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        if (! $this->active()) {
            return false;
        }

        $subscription = ChargebeeSubscription::update($this->id, [
            'billingCycles' => $billingCycles,
            'endOfTerm' => $endofTerm,
        ])->subscription();

        $this->update([
            'plan_id' => $subscription->planId,
            'billing_period' => $subscription->billingPeriod,
            'billing_period_unit' => $subscription->billingPeriodUnit,
            'plan_quantity' => $subscription->planQuantity,
            'plan_free_quantity' => $subscription->planFreeQuantity,
            'starts_at' => $subscription->startedAt,
            'trial_starts_at' => $subscription->trialStart,
            'trial_ends_at' => $subscription->trialEnd,
            'next_billing_at' => $subscription->nextBillingAt,
            'status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Change the trial ending for a subscription.
     *
     * @param string $date
     * @return bool|\Rennokki\Chargeswarm\Models\Subscription
     */
    public function changeTrialEnd($date = null)
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        if (! $this->active()) {
            return false;
        }

        $date = ($date) ? Carbon::parse($date) : null;

        $subscription = ChargebeeSubscription::update($this->id, [
            'trialEnd' => $date->format('U'),
        ])->subscription();

        $this->update([
            'plan_id' => $subscription->planId,
            'billing_period' => $subscription->billingPeriod,
            'billing_period_unit' => $subscription->billingPeriodUnit,
            'plan_quantity' => $subscription->planQuantity,
            'plan_free_quantity' => $subscription->planFreeQuantity,
            'starts_at' => $subscription->startedAt,
            'trial_starts_at' => $subscription->trialStart,
            'trial_ends_at' => $subscription->trialEnd,
            'next_billing_at' => $subscription->nextBillingAt,
            'status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Change the ending term for this subscription.
     *
     * @param string|null $date
     * @return bool|\Rennokki\Chargeswarm\Models\Subscription
     */
    public function changeTermEnd($date = null)
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        if (! $this->active()) {
            return false;
        }

        $date = ($date) ? Carbon::parse($date) : null;

        $subscription = ChargebeeSubscription::changeTermEnd($this->id, [
            'termEndsAt' => $date->format('U'),
        ])->subscription();

        $this->update([
            'plan_id' => $subscription->planId,
            'billing_period' => $subscription->billingPeriod,
            'billing_period_unit' => $subscription->billingPeriodUnit,
            'plan_quantity' => $subscription->planQuantity,
            'plan_free_quantity' => $subscription->planFreeQuantity,
            'starts_at' => $subscription->startedAt,
            'trial_starts_at' => $subscription->trialStart,
            'trial_ends_at' => $subscription->trialEnd,
            'next_billing_at' => $subscription->nextBillingAt,
            'status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Soft cancel the subscription. It can be later reactivated.
     *
     * @return bool|\Rennokki\Chargeswarm\Models\Subscription
     */
    public function cancel()
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        if (! $this->active() || ! $this->onTrial()) {
            return false;
        }

        $subscription = ChargebeeSubscription::cancel($this->id, [
            'endOfTerm' => true,
        ])->subscription();

        $this->update([
            'plan_id' => $subscription->planId,
            'billing_period' => $subscription->billingPeriod,
            'billing_period_unit' => $subscription->billingPeriodUnit,
            'plan_quantity' => $subscription->planQuantity,
            'plan_free_quantity' => $subscription->planFreeQuantity,
            'starts_at' => $subscription->startedAt,
            'ends_at' => $subscription->cancelledAt,
            'trial_starts_at' => $subscription->trialStart,
            'trial_ends_at' => $subscription->trialEnd,
            'next_billing_at' => $subscription->nextBillingAt,
            'status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Cancel without any other way of reactivating (if it has trial).
     *
     * @return bool|\Rennokki\Chargeswarm\Models\Subscription
     */
    public function cancelImmediately()
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        if (! $this->active()) {
            return false;
        }

        $subscription = ChargebeeSubscription::cancel($this->id, [
            'endOfTerm' => false,
        ])->subscription();

        $this->update([
            'plan_id' => $subscription->planId,
            'billing_period' => $subscription->billingPeriod,
            'billing_period_unit' => $subscription->billingPeriodUnit,
            'plan_quantity' => $subscription->planQuantity,
            'plan_free_quantity' => $subscription->planFreeQuantity,
            'starts_at' => $subscription->startedAt,
            'ends_at' => $subscription->cancelledAt,
            'trial_ends_at' => $subscription->cancelledAt,
            'status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Resume the trial for the current subscription.
     *
     * @return \Rennokki\Chargeswarm\Models\Subscription
     */
    public function resume()
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        $subscription = ChargebeeSubscription::removeScheduledCancellation($this->id)->subscription();

        $this->update([
            'plan_id' => $subscription->planId,
            'billing_period' => $subscription->billingPeriod,
            'billing_period_unit' => $subscription->billingPeriodUnit,
            'plan_quantity' => $subscription->planQuantity,
            'plan_free_quantity' => $subscription->planFreeQuantity,
            'starts_at' => $subscription->startedAt,
            'ends_at' => null,
            'trial_starts_at' => $subscription->trialStart,
            'trial_ends_at' => $subscription->trialEnd,
            'next_billing_at' => $subscription->nextBillingAt,
            'status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Reactivate the current subscription.
     *
     * @return bool|\Rennokki\Chargeswarm\Models\Subscription
     */
    public function reactivate()
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        if (! $this->cancelled()) {
            return false;
        }

        $subscription = ChargebeeSubscription::reactivate($this->id)->subscription();

        $this->update([
            'plan_id' => $subscription->planId,
            'billing_period' => $subscription->billingPeriod,
            'billing_period_unit' => $subscription->billingPeriodUnit,
            'plan_quantity' => $subscription->planQuantity,
            'plan_free_quantity' => $subscription->planFreeQuantity,
            'starts_at' => $subscription->startedAt,
            'ends_at' => null,
            'trial_starts_at' => $subscription->trialStart,
            'trial_ends_at' => $subscription->trialEnd,
            'next_billing_at' => $subscription->nextBillingAt,
            'status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Check if the subscription is cancelled.
     *
     * @return bool
     */
    public function cancelled()
    {
        return (bool) ! is_null($this->ends_at);
    }

    /**
     * Check if the subscription is active.
     *
     * @return bool
     */
    public function active()
    {
        if (! $this->valid()) {
            return $this->onTrial();
        }

        return true;
    }

    /**
     * Check if the subscription is on trial.
     *
     * @return bool
     */
    public function onTrial()
    {
        if (! is_null($this->trial_ends_at)) {
            return Carbon::now()->addSecond()->lte(Carbon::parse($this->trial_ends_at));
        }

        return false;
    }

    /**
     * Check if the subscription is valid (has not ended).
     *
     * @return bool
     */
    public function valid()
    {
        if (! $this->ends_at) {
            return true;
        }

        return Carbon::now()->addSecond()->lte(Carbon::parse($this->ends_at));
    }

    /**
     * Create a new usage for the current subscription.
     *
     * @param string $metadataId
     * @param float $total
     * @return \Rennokki\Chargeswarm\Models\SubscriptionUsage
     */
    public function createUsage(string $metadataId, float $total)
    {
        $usage = $this->usages()->metadata($metadataId)->first();

        if ($usage) {
            return $usage;
        }

        return $this->usages()->create([
            'metadata_id' => $metadataId,
            'total' => $total,
        ]);
    }

    /**
     * Consume a countable feature.
     *
     * @param string $metadataId
     * @param float $amount
     * @return bool
     */
    public function consume(string $metadataId, float $amount = 1)
    {
        $usage = $this->usages()->metadata($metadataId)->first();

        if (! $usage || $usage->remaining() < $amount) {
            return false;
        }

        return (bool) $usage->increment('used', $amount);
    }

    /**
     * Unconsume the tracking of a feature.
     *
     * @param string $metadataId
     * @param float $amount
     * @return bool
     */
    public function unconsume(string $metadataId, float $amount = 1)
    {
        $usage = $this->usages()->metadata($metadataId)->first();

        if (! $usage) {
            return false;
        }

        return (bool) (($usage->used < $amount) ? $usage->update(['used' => 0]) : $usage->decrement('used', $amount));
    }
}
