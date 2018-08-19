[![Build Status](https://travis-ci.org/rennokki/chargeswarm.svg?branch=master)](https://travis-ci.org/rennokki/chargeswarm)
[![codecov](https://codecov.io/gh/rennokki/chargeswarm/branch/master/graph/badge.svg)](https://codecov.io/gh/rennokki/chargeswarm/branch/master)
[![StyleCI](https://github.styleci.io/repos/145119007/shield?branch=master)](https://github.styleci.io/repos/143601238)
[![Latest Stable Version](https://poser.pugx.org/rennokki/chargeswarm/v/stable)](https://packagist.org/packages/rennokki/chargeswarm)
[![Total Downloads](https://poser.pugx.org/rennokki/chargeswarm/downloads)](https://packagist.org/packages/rennokki/chargeswarm)
[![Monthly Downloads](https://poser.pugx.org/rennokki/chargeswarm/d/monthly)](https://packagist.org/packages/rennokki/chargeswarm)
[![License](https://poser.pugx.org/rennokki/chargeswarm/license)](https://packagist.org/packages/rennokki/chargeswarm)

[![PayPal](https://img.shields.io/badge/PayPal-donate-blue.svg)](https://paypal.me/rennokki)

# Laravel Chargeswarm
Laravel Chargeswarm is a Laravel Cashier-alike package that will help you befriend the bees and have a great SaaS system for your app. This package provides methods to create, update, cancel or resume subscriptions and also to handle the webhooks in style!

# Advantages of Chargebee
Chargebee is not a payment provider. In fact, Chargebee is a manager for SaaS, while you can use any kind of payment gateway. The same as Stripe, you can fully use features like [Chargebee's metadata](https://www.chargebee.com/docs/metadata.html) to carry out information for your plans.

This package also supports tracking consumption for countable features. Stay tuned until the end of the documentation to know what's all about.

# Installation
Install the package:
```bash
$ composer require rennokki/chargeswarm
```

If your Laravel version does not support package discovery, add this line in the `providers` array in your `config/app.php` file:
```php
Rennokki\Chargeswarm\ChargeswarmServiceProvider::class,
```

Publish the config file & migration files:
```bash
$ php artisan vendor:publish
```

Migrate the database:
```bash
$ php artisan migrate
```

Add the `Billable` trait to your Eloquent model:
```php
use Rennokki\Chargeswarm\Traits\Billable;

class User extends Model {
    use Billable;
    ...
}
```

Do not forget to add your site & your API key, as well as the gateway option in your `.env` file:
```
CHARGEBEE_SITE=site-test
CHARGEBEE_KEY=test_...
CHARGEBEE_GATEWAY=stripe
```

# Usage
If you are familiar with Cashier's source code, this is kinda' close as structure. To subscribe your users, we'll use a subscription builder. In any other cases, we'll be using methods called from each subscription.

Any of the fields are optional, with the exception of the `plan_id` parameter and `create` method.
```php
$subscription = $user->subscription('plan_id')
                     ->withCoupon('coupon')
                     ->withAddons(['addon1', 'addon2'])
                     ->billingCycles(12)
                     ->withQuantity(3)
                     ->create('stripe_or_braintree_token');

$user->subscribed('plan_id'); // true
$user->activeSubscriptions()->count(); // 1
```

Also, if you plan to add some more data to your customer, use the `withCustomerData()` method. All fields are optional and can be set to `null`:
```php
$user->subscription('plan_id')
     ->withCustomerData('email@google.com', 'John', 'Smith', 'Company Name')
     ->...
     ->create('token');
```

If you also plan on adding billing details, this one's a bit much longer. If you don't want to use certain fields, set them to `null`.
```php
$user->subscription('plan_id')
     ->withCustomerData('email@google.com', 'John', 'Smith')
     ->withBilling(
        'email@google.com', 'John', 'Smith',
        'Street...', 'City', 'State',
        'Zip code', 'Country', 'Company name'
     )
     ->...
     ->create('token');
```

# Swap to another plan
You can simply swap a subscription's plan using the `swap()` method called within the subscription. If the subscription is not active, it will return false. 
```php
$subscription = $user->activeSubscriptions()->first();
$subscription = $subscription->swap('new_plan_id'); // updated subscription
```

# Cancelling & Resuming subscriptions
You can set your plans to be on trial. If you plan to cancel a subscription, you can do so using the `cancel()` method. However, if the subscription is not expired (the expiration date did not pass), it will still be available through the trial, but it would be marked as cancelled.
```php
$subscripton->cancel();
$subscription->cancelled(); // true
```

It can later be resumed, if the user decides to go on with the subscription:
```php
$subscription->resume();
$subscription->active(); // true
$subscription->onTrial(); // true
```

Cancelling it immediately would cancel the subscription without being able to be resumed again:
```php
$subscription->cancelImmediately();
$subscription->active(); // false
$subscription->onTrial(); // false
```

However, the cancelled subscription can be `reactivated` instead of `resumed`:
```php
$subscription->reactivate();
$subscription->active(); // true
```

# Webhooks
Anytime something happens, Chargebee will send a `POST` request to a configured webhook. Fortunately, Chargeswarm can do this for you and has a ton of support when it comes to webhooks.

To handle all Chargebee's webhooks automatically, all you have to do is to declare a route like this in your `routes/web.php` or `routes/api.php` file with the following controller:
```php
Route::post('/webhooks/chargebee', '\Rennokki\Chargeswarm\Http\Controllers\ChargebeeWebhookController@handleWebhook');
```

Also, in case you have CSRF protection on, make sure you disable it in your `VerifyCsrfToken.php` file:
```php
protected $except = [
    'webhooks/chargebee',
];
```

# Pre-defined webhooks
There are 27 events & pre-defined webhooks, but you can extend it using [any of the Chargebee's events](https://apidocs.chargebee.com/docs/api/events#event_types) due to friendly syntax that will be explained later. Each time a webhook fires, no matter the event, you will receive a `\Rennokki\Chargeswarm\Events\WebhookReceived` event that carries out as variable an `$event->payload` JSON object.

Additionally, for these 23 pre-defined webhooks, you will also receive a specific event. You can find a list of [pre-defined webhooks and their paired events here](webhooks.md).

Unfortunately, for any other class method you declare, other than those defined earlier, you will not receive events. The only event that triggers is the `\Rennokki\Chargeswarm\Events\WebhookReceived` event, which triggers automatically on each webhook received.

By default, the following controller methods automatically do the logic for your plans. I recommend **NOT** overwriting these unless you know what you do:
* `handleSubscriptionCancelled`
* `handlePaymentSucceeded`
* `handlePaymentRefunded`
* `handleSubscriptionDeleted`
* `handleSubscriptionRenewed`
* `handleInvoiceGenerated`
* `handleInvoiceCreated`
* `handleInvoiceDeleted`

For these four, instead, i recommend listening their **paired events** to handle your own logic. In case you want to implement any other handler, you are free to do it by extending the controller, but remember that events associated with the hooks are also triggered.

# Extending the Controller
Customizing webhooks can be done simply by extending your controller from `Rennokki\Chargeswarm\Http\Controllers\ChargebeeWebhookController`:
```php
use Rennokki\Chargeswarm\Http\Controllers\ChargebeeWebhookController;

class MyController extends ChargebeeWebhookController
{
    public function handleSubscriptionResumed($payload, $storedSubscription, $subscription)
    {
        // $payload is the JSON Object with the request
        // $storedSubscription is the stored subscription (if any)
        // $subscription is the subscription data (equivalent of $payload->content->subscription), if any
    }
}
```

After extending it, make sure you are using your controller with the same `@handleWebhook` method:
```php
Route::post('/webhooks/chargebee', 'MyController@handleWebhook');
```

# Customizing webhooks
You can customize any kind of method in your controller that follows the following rule:
```
MyController@handle{EventNameInStudlyCase}($payload, $storedSubscription, $subscription)
```

For example, since `card_added` Chargebee event is not pre-defined nor added, you can simply add this method in your controller:
```php
public function handleCardAdded($payload, $storedSubscription, $subscription)
{
    // your logic here
    // only $payload is not null.
    // The rest of the variables injected can be null or not, if the 
    // subscription object exists
}
```

All controller methods and events accept 3 parameters: `$payload`, `$storedSubscription` and `$subscription`.

# Events
As stated earlier, the `\Rennokki\Chargeswarm\Events\WebhookReceived` event fires automatically. In addition to that, [each of the listed method here automatically fires the paired event](webhooks.md).

If you are not familiar with events, [check Laravel's Official Documentation on Events](https://laravel.com/docs/5.6/events) that teaches you what are events, how to handle them and, more important, how to listen to them.

All events send 3 parameters to their listeners: `$payload`, `$storedSubscription` and `$subscription`. They can be accessed in your listener using the event instance.

For example, each time the `@handleSubscriptionResumed` is called, we can listen to the `\Rennokki\Chargeswarm\Events\SubscriptionResumed` event and implement our logic:
```php
class MyListener {
    public function handle(SubscriptionResumed $event)
    {
        // $event->payload
        // $event->storedSubscription
        // $event->subscription
    }
}
```

# Countable features
Let's say you run your own newsletters app that bills users using SaaS and you give your users `5.000` newsletters, monthly, that they can send.

On subscribing or after the subscription renews (which can be done by listening to the `\Rennokki\Chargeswarm\Events\SubscriptionRenewed` event), you can simply call `createUsage()` in your logic, for example:
```php
public function handle()
{
    $subscription = $event->storedSubscription;
    $subscription->createUsage('monthly.emails', 5000);
    
    ...
}
```

Later, you can `consume` or `unconsume` them all around your app by calling the methods within the subscription:
```php
$subscription->consume('monthly.emails', 10); // sent 10 mails
```

Reversing the effect can be useful in cases with errors, for example. When the mailserver fails, but your app doesn't. To reverse it, you can unconsume it:
```php
$subscription->unconsume('monthly.emails', 10); // undo-ed 10 from the quota
```

Consuming or unconsuming inexistent, unset, usages will give you a `false`. Also, if the amount consumed is higher than the one remaining, you will also get a `false`.
```php
$subscription->consume('daily.emails', 10); // false
```

Unconsuming does not falls below zero. If you unconsume more than you have used, the overflow won't hit and the `used` attribute will be set to 0.

If you plan receiving all the usages, you can do so by calling the `usages()` relationship within the subscription:
```php
$usages = $subscription->usages()->get();
```
