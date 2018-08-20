<?php

namespace Rennokki\Chargeswarm\Test;

use Carbon\Carbon;
use Chargebee_Plan as ChargebeePlan;
use Chargebee_Invoice as ChargebeeInvoice;
use Chargebee_Download as ChargebeeDownload;
use Chargebee_ListResult as ChargebeeListResult;
use Chargebee_InvalidRequestException as ChargebeeInvalidRequestException;

class SubscriptionTest extends TestCase
{
    public function testCreateCancelResumeCancelImmediately()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_hustle')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->startsOn(Carbon::now())
             ->create('tok_visa');

        $this->assertTrue($user->subscribed('cbdemo_hustle'));
        $this->assertFalse($user->subscribed('1'));
        $this->assertEquals($user->activeSubscriptions()->count(), 1);

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();
        $this->assertEquals($subscription->plan_id, 'cbdemo_hustle');

        $this->assertNull($subscription->trial_starts_at);
        $this->assertNull($subscription->trial_ends_at);

        $this->assertFalse($subscription->onTrial());
        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->valid());

        $subscription->cancel();
        $subscription->refresh();
        $this->assertFalse($subscription->cancelled());
        $this->assertNull($subscription->ends_at);

        try {
            $subscription->resume();
        } catch (ChargebeeInvalidRequestException $e) {
            $this->assertTrue(true);
        }

        $subscription->cancelImmediately();
        $subscription->refresh();
        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->onTrial());

        $subscription->reactivate();
        $subscription->refresh();
        $this->assertFalse($subscription->cancelled());
        $this->assertTrue($subscription->active());
        $this->assertFalse($subscription->onTrial());
    }

    public function testCreateCancelResumeCancelImmediatelyWithTrialPlan()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_grow')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->startsOn(Carbon::now())
             ->onTrial()
             ->trialEndsOn(Carbon::now()->addDays(14))
             ->create('tok_visa');

        $this->assertTrue($user->subscribed('cbdemo_grow'));
        $this->assertFalse($user->subscribed('1'));
        $this->assertEquals($user->activeSubscriptions()->count(), 1);

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();
        $this->assertEquals($subscription->plan_id, 'cbdemo_grow');

        $this->assertTrue($subscription->onTrial());
        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->valid());

        $subscription->cancel();
        $subscription->refresh();
        $this->assertTrue($subscription->cancelled());
        $this->assertTrue(Carbon::parse($subscription->ends_at)->equalTo(Carbon::parse($subscription->trial_ends_at)));

        $subscription->resume();
        $subscription->refresh();
        $this->assertFalse($subscription->cancelled());
        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->onTrial());

        $subscription->cancelImmediately();
        $subscription->refresh();
        $this->assertTrue($subscription->cancelled());
        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->onTrial());

        $subscription->reactivate();
        $subscription->refresh();
        $this->assertFalse($subscription->cancelled());
        $this->assertTrue($subscription->active());
        $this->assertFalse($subscription->onTrial());
    }

    public function testSwapToTrial()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_hustle')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->create('tok_visa');

        $this->assertTrue($user->subscribed('cbdemo_hustle'));
        $this->assertFalse($user->subscribed('1'));
        $this->assertEquals($user->activeSubscriptions()->count(), 1);

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();
        $this->assertEquals($subscription->plan_id, 'cbdemo_hustle');

        $subscription->swap('cbdemo_grow');
        $subscription->refresh();

        $subscription->updateBillingCycles(13);
        $subscription->refresh();

        $this->assertEquals($subscription->plan_id, 'cbdemo_grow');

        $subscription->changeTrialEnd(0);

        $subscription->refresh();
        $this->assertFalse($subscription->onTrial());
        $this->assertTrue($subscription->active());
    }

    public function testSwapToPaid()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_grow')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->create('tok_visa');

        $this->assertTrue($user->subscribed('cbdemo_grow'));
        $this->assertFalse($user->subscribed('1'));
        $this->assertEquals($user->activeSubscriptions()->count(), 1);

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();
        $this->assertEquals($subscription->plan_id, 'cbdemo_grow');

        $subscription->swap('cbdemo_hustle');
        $subscription->refresh();

        $subscription->updateBillingCycles(13);
        $subscription->refresh();
        $this->assertEquals($subscription->plan_id, 'cbdemo_hustle');

        $subscription->changeTermEnd(Carbon::tomorrow());
        $subscription->refresh();
        $this->assertFalse($subscription->onTrial());
        $this->assertTrue($subscription->active());
    }

    public function testCoupon()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_hustle')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->withCoupon('cbdemo_earlybird')
             ->create('tok_visa');

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();

        $this->assertNotNull($subscription->id);
    }

    public function testAddon()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_hustle')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->withAddons('cbdemo_additionaluser')
             ->create('tok_visa');

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();

        $this->assertInstanceOf(Carbon::class, $subscription->next_billing_at);
        $this->assertNotNull($subscription->id);
    }

    public function testInvoice()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_hustle')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->withAddons('cbdemo_additionaluser')
             ->create('tok_visa');

        $activeSubscriptions = $user->activeSubscriptions();
        $subscription = $activeSubscriptions->first();
        $invoices = $subscription->invoices();
        $plan = $subscription->plan();

        $this->assertInstanceOf(ChargebeePlan::class, $plan);
        $this->assertEquals($subscription->plan_id, $plan->id);

        $this->assertInstanceOf(ChargebeeListResult::class, $invoices);
        $this->assertEquals($invoices->count(), 1);

        $invoices = $user->invoices($subscription->id);

        $this->assertInstanceOf(ChargebeeListResult::class, $invoices);
        $this->assertEquals($invoices->count(), 1);

        foreach ($invoices as $invoice) {
            $invoice = $invoice->invoice();
        }

        $this->assertInstanceOf(ChargebeeInvoice::class, $user->invoice($invoice->id));
        $this->assertInstanceOf(ChargebeeDownload::class, $user->downloadInvoice($invoice->id));
    }
}
