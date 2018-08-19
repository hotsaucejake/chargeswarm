<?php

namespace Rennokki\Chargeswarm\Test;

class UsageTest extends TestCase
{
    public function testUsage()
    {
        $user = factory(\Rennokki\Chargeswarm\Test\Models\User::class)->create();

        $user->subscription('cbdemo_hustle')
             ->withCustomerData('a@b.com', 'First Name', 'Last Name')
             ->withBilling('a@c.com', 'First', 'Last', 'Address', 'City', 'State', null, 'RO', 'Company')
             ->billingCycles(12)
             ->create('tok_visa');

        $subscription = $user->activeSubscriptions()->first();

        $this->assertFalse($subscription->consume('build.hours'));
        $this->assertFalse($subscription->unconsume('build.hours'));

        $this->assertNotNull($subscription->createUsage('build.minutes', 1000));
        $this->assertTrue($subscription->consume('build.minutes'));
        $this->assertTrue($subscription->consume('build.minutes', 999));
        $this->assertFalse($subscription->consume('build.minutes'));

        $this->assertTrue($subscription->unconsume('build.minutes'));
        $this->assertTrue($subscription->unconsume('build.minutes', 999));
        $this->assertTrue($subscription->unconsume('build.minutes'));
    }
}
