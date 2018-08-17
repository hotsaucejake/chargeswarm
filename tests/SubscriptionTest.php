<?php

namespace Rennokki\Chargeswarm\Test;

use Carbon\Carbon;

class SubscriptionTest extends TestCase
{
    public function testCreateCancelResumeCancelImmediately()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_hustle')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->create('fake-valid-visa-nonce');

        $this->assertTrue($user->subscribed('cbdemo_hustle'));
        $this->assertFalse($user->subscribed('1'));
        $this->assertEquals($user->activeSubscriptions()->count(), 1);

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();
        $this->assertEquals($subscription->plan_id, 'cbdemo_hustle');

        $this->assertTrue($subscription->onTrial());
        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->valid());

        $subscription->cancel();
        $this->assertTrue($subscription->cancelled());
        $this->assertTrue(Carbon::parse($subscription->ends_at)->equalTo(Carbon::parse($subscription->trial_ends_at)));

        $subscription->resume();
        $this->assertFalse($subscription->cancelled());
        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->onTrial());

        $subscription->cancelImmediately();
        $this->assertTrue(Carbon::now()->gte(Carbon::parse($subscription->ends_at)));
        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->onTrial());
    }

    public function testCoupon()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_hustle')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->withCoupon('cbdemo_earlybird')
             ->create('fake-valid-visa-nonce');

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();

        $this->assertNotNull($subscription->subscription_id);
    }

    public function testAddon()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_hustle')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->withAddons('cbdemo_additionaluser')
             ->create('fake-valid-visa-nonce');

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();

        $this->assertInstanceOf(Carbon::class, $subscription->next_billing_at);
        $this->assertNotNull($subscription->subscription_id);
    }
}
