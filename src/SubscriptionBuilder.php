<?php

namespace Rennokki\Chargeswarm;

use Carbon\Carbon;
use ChargeBee_Environment as ChargebeeEnvironment;
use ChargeBee_Subscription as ChargebeeSubscription;

class SubscriptionBuilder
{
    protected $model = null;
    protected $planId = null;
    protected $addons = [];
    protected $couponId = null;
    protected $billingCycles = 1;
    protected $quantity = 1;
    protected $affiliateToken = null;
    protected $startDate = null;
    protected $trialEnd = null;
    protected $trial = false;

    protected $customerEmail;
    protected $customerFirstName;
    protected $customerLastName;
    protected $customerCompany;

    protected $billingEmail;
    protected $billingFirstName;
    protected $billingLastName;
    protected $billingAddress;
    protected $billingCity;
    protected $billingState;
    protected $billingZip;
    protected $billingCountry;
    protected $billingCompany;

    protected $invoiceNotes = null;

    public function __construct($model = null, $planId = null)
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        $this->model = $model;
        $this->planId = $planId;

        $this->addons = collect($this->addons);
    }

    /**
     * Set a default starting date.
     *
     * @param string|null $date
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function startsOn($date = null)
    {
        $this->startDate = (! is_null($date)) ? Carbon::parse($date) : null;

        return $this;
    }

    /**
     * Overwrite the settings and start this subscription as trial.
     *
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function onTrial()
    {
        $this->trial = true;

        return $this;
    }

    /**
     * Overwrite the settings and specify a trial ending date.
     *
     * @param string|null $date
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function trialEndsOn($date = null)
    {
        $this->trialEnd = (! is_null($date)) ? Carbon::parse($date) : null;

        return $this;
    }

    public function withInvoiceNotes($notes = null)
    {
        $this->invoiceNotes = $notes;

        return $this;
    }

    /**
     * Add a coupon to the subscription.
     *
     * @param string $couponId
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function withCoupon(string $couponId)
    {
        $this->couponId = $couponId;

        return $this;
    }

    /**
     * Add addons to the subscription.
     *
     * @param string $couponId
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function withAddons($addons)
    {
        $this->addons = $this->addons->merge((is_string($addons)) ? [$addons] : $addons);

        return $this;
    }

    /**
     * Add customer data.
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $company
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function withCustomerData($email = null, $firstName = null, $lastName = null, $company = null)
    {
        $this->customerEmail = $email;
        $this->customerFirstName = $firstName;
        $this->customerLastName = $lastName;
        $this->customerCompany = $company;

        return $this;
    }

    /**
     * Add billing details.
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $address
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param string $country
     * @param string $company
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function withBilling($email = null, $firstName = null, $lastName = null, $address = null, $city = null, $state = null, $zip = null, $country = null, $company = null)
    {
        $this->billingEmail = $email;
        $this->billingFirstName = $firstName;
        $this->billingLastName = $lastName;
        $this->billingAddress = $address;
        $this->billingCity = $city;
        $this->billingState = $state;
        $this->billingZip = $zip;
        $this->billingCountry = $country;
        $this->billingCompany = $company;

        return $this;
    }

    /**
     * Add affiliate token.
     *
     * @param string $affiliateToken
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function withAffiliate(string $affiliateToken)
    {
        $this->affiliateToken = $affiliateToken;

        return $this;
    }

    /**
     * Set the billing cycles.
     *
     * @param int $billingCycles
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function billingCycles(int $billingCycles = 1)
    {
        $this->billingCycles = $billingCycles;

        return $this;
    }

    /**
     * Set the quantity.
     *
     * @param int $quantity
     * @return \Rennokki\Chargeswarm\SubscriptionBuilder
     */
    public function withQuantity(int $quantity = 1)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Issue a new subscription.
     *
     * @param string $cardToken
     * @return \Rennokki\Chargeswarm\Models\Subscription
     */
    public function create($cardToken = null)
    {
        $subscription = $this->buildSubscription($cardToken);
        $result = ChargebeeSubscription::create($subscription->toArray());

        $subscription = $result->subscription();
        $card = $result->card();
        $invoice = $result->invoice();

        $storedSubscription = $this->model->subscriptions()->create([
            'id' => $subscription->id,
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
            'last_four' => ($card) ? $card->last4 : null,
        ]);

        return $storedSubscription;
    }

    /**
     * Build the subscription for the API.
     *
     * @param string $cardToken
     * @return \Illuminate\Support\Collection
     */
    public function buildSubscription($cardToken = null)
    {
        $subscription = collect([]);

        $subscription->put('planId', $this->planId);
        $subscription->put('planQuantity', $this->quantity);
        $subscription->put('addons', $this->addons->toArray());
        $subscription->put('couponId', $this->couponId);
        $subscription->put('billingCycles', $this->billingCycles);

        $subscription->put('customer', [
            'firstName' => $this->customerFirstName,
            'lastName' => $this->customerLastName,
            'email' => $this->customerEmail,
            'company' => $this->customerCompany,
        ]);

        $subscription->put('billingAddress', [
            'firstName' => $this->billingFirstName,
            'lastName' => $this->billingLastName,
            'email' => $this->billingEmail,
            'address' => $this->billingAddress,
            'city' => $this->billingCity,
            'state' => $this->billingState,
            'zip' => $this->billingZip,
            'country' => $this->billingCountry,
            'company' => $this->billingCompany,
        ]);

        if ($cardToken) {
            $subscription->put('card', [
                'gateway' => (getenv('CHARGEBEE_GATEWAY')) ?: env('CHARGEBEE_GATEWAY', ''),
                'tmpToken' => $cardToken,
            ]);
        }

        $subscription->put('affiliateToken', $this->affiliateToken);
        $subscription->put('invoiceNotes', $this->invoiceNotes);
        $subscription->put('startDate', ($this->startDate) ? $this->startDate->format('U') : null);
        $subscription->put('trialEnd', ($this->trialEnd) ? $this->trialEnd->format('U') : null);

        return $subscription;
    }
}
