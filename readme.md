[![Build Status](https://travis-ci.org/rennokki/chargeswarm.svg?branch=master)](https://travis-ci.org/rennokki/chargeswarm)
[![codecov](https://codecov.io/gh/rennokki/chargeswarm/branch/master/graph/badge.svg)](https://codecov.io/gh/rennokki/chargeswarm/branch/master)
[![StyleCI](https://github.styleci.io/repos/145119007/shield?branch=master)](https://github.styleci.io/repos/143601238)
[![Latest Stable Version](https://poser.pugx.org/rennokki/chargeswarm/v/stable)](https://packagist.org/packages/rennokki/chargeswarm)
[![Total Downloads](https://poser.pugx.org/rennokki/chargeswarm/downloads)](https://packagist.org/packages/rennokki/chargeswarm)
[![Monthly Downloads](https://poser.pugx.org/rennokki/chargeswarm/d/monthly)](https://packagist.org/packages/rennokki/chargeswarm)
[![License](https://poser.pugx.org/rennokki/chargeswarm/license)](https://packagist.org/packages/rennokki/chargeswarm)

[![PayPal](https://img.shields.io/badge/PayPal-donate-blue.svg)](https://paypal.me/rennokki)

# Laravel Chargeswarm
Laravel Chargeswarm is a Laravel wrapper for the Chargebee service's API. Chargebee is a service that helps you with tracking subscriptions, issuing invoices for your services and any other SaaS-like activity. This package wraps around the Subscription endpoints that helps you manage easy your plans and subscriptions and also gives you an easy, basic, setup to track countable features for your plans.

# Advantages of Chargebee
Chargebee does not act as a payment provider that also handles subscriptions, but also helps you install a payment Gateway such as Stripe or Braintree and then use the API to provide an easy access to a SaaS manager.

While you can use [Chargebee's metadata](https://www.chargebee.com/docs/metadata.html) to carry out information for your plans, this package also provides support to track `countable features` for your plans. This feature will be exaplained later in-depth. 

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
CHARGEBEE_SITE=test-renoki
CHARGEBEE_KEY=sk_test_...
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
Most of the plans are in trial. If you plan to cancel a subscription, you can do so using the `cancel()` method. However, if the subscription is not expired (the expiration date did not pass), it will still be available, but it would be marked as cancelled. It can later be resumed, if the user is deciding to go on.
```php
$subscripton->cancel();
$subscription->cancelled(); // true
```

If the user decides to resume the subscription, it can do so:
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

However, the cancelled subscription can be `reactivated`:
```php
$subscription->reactivate();
$subscription->active(); // true
```

# Webhooks & Events
The most used feature encountered is the Webhook. Anytime something happens, Chargebee will send a `POST` request to a configured webhook. Fortunately, Chargeswarm can do this for you and has a ton of support when it comes to webhooks & events.

If you are not familiar with Laravel Events, check out the [Official Laravel Documentation on Events](https://laravel.com/docs/5.6/events)

Getting started - all you have to do is to declare a route like this in your `routes/web.php` or `routes/api.php` file:
```php
Route::post('/webhooks/chargebee', '\Rennokki\Chargeswarm\Http\Controllers\ChargebeeWebhookController@handleWebhook');
```

Also, in case you have CSRF protection on, make sure you disable it in your `VerifyCsrfToken.php` file:
```php
protected $except = [
    'webhooks/chargebee',
];
```

Currently, there are **23** events that can be configured simply by extending the previously used controller and implementing your own logic.
By default, `handleSubscriptionCancelled`, `handlePaymentSucceeded`, `handleSubscriptionDeleted` and `handleSubscriptionRenewed` automatically do the logic for your plans. I recommend **NOT** overwriting these unless you know what you do. For these four, use their **paired events** to handle your own logic. In case you want to implement any other handler, you are free to do it by extending the controller, but remember that events associated with the hooks are also triggered.
```php
use Rennokki\Chargeswarm\Http\Controllers\ChargebeeWebhookController;

class MyController extends ChargebeeWebhookController {
    public function handleSubscriptionResumed($payload, $storedSubscription, $subscription)
    {
        // $payload is the JSON Object with the request
        // $storedSubscription is the stored subscription (if any)
        // $subscription is the subscription data (equivalent of $payload->content->subscription
    }
}
```

After extending it, make sure you are using your controller with the same `@handleWebhook` method:
```php
Route::post('/webhooks/chargebee', 'MyController@handleWebhook');
```

These are all available methods that are called via the webhook with their pair events. The ones marked with the asterisk* are already implemented and should **NOT** be overwritten (unless you know what you are doing).

All methods and events accept 3 parameters: `$payload`, `$storedSubscription` and `$subscription`. All of them are explained in the previous example with Controller extension.

**Since there are **23** events and applied webhooks, the whole table with all webhooks and paired events can be found [in the webhooks.md file](webhooks.md).**

For example, if you implement your own `@handleSubscriptionResumed` like before, by extending the provided controller in `MyController`, every time the `handleSubscriptionResumed` method is called by the webhook, the paired `\Rennokki\Chargeswarm\Events\SubscriptionResumed` event will fire and you can attach your own listeners and run your logic.

For customizability, the `\Rennokki\Chargeswarm\Events\WebhookReceived` event is launched every time Chargebee hits the webhook and accepts only one parameter which is `payload` and a listener can easily do the job for you:
```php
...
public function handle(WebhookReceived $event)
{
    $payload = $event->payload;
    $subscription = $payload->content->subscription;
    
    if ($subscription) {
        ...
    }
}
```

# Other Chargebee Webhook events
Chargeswarm implements only 23 webhook handlers. [There are many more events that can be used](https://apidocs.chargebee.com/docs/api/events#event_types), and each of the Chargebee event follows one simple rule.

For example, for `card_added` event, your controller method can be `handleCardAdded($payload, $localSubscription, $subscription)`.

Unfortunately, no events are triggered and even if `$localSubscription` and `$subscription` can be `null`, you have to declare them.

```php
class MyController extends ChargebeeWebhookController {
    public function handleCardAdded($payload, $storedSubscription, $subscription)
    {
        // $storedSubscription and $subscription are null
    }
}
```

# Countable features
In apps that heavily use a SaaS system, you will often see that the greater the price is, the more features you get. In some cases, you will find out that with higher plans, you get access to new modules of the app. In this particular case, you can have, for example, countable features.

Let's say you run your own app that is billed using SaaS and you give your users `5.000` newsletters they can send.

On subscribing or after the subscription renews, you can simply call `createUsage()` from the subscription:
```php
$subscription->createUsage('monthly.emails', 5000);
```

Later, you can `consume` or `unconsume` them all around your app:
```php
$subscription->consume('montly.emails', 10); // sent 10 mails
```

If you had problems with your servers and the mails were not sent, but the user claims it still has 10 less mails in their montly quota, you can undo this action:
```php
$subscription->unconsume('monthly.emails', 10); // undo-ed 10 from the quota
```

Consuming or unconsuming inexistent usages, you will get `false`:
```php
$subscription->consume('daily.emails', 10); // false
```

**Keep in mind: if you consume more than you have left you get also `false` and if you unconsume until it drops below 0, it will not go to negative values.**
