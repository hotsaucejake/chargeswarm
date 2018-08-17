| Controller method                               | Event class                                                                  |
|-------------------------------------------------|------------------------------------------------------------------------------|
| @handleWebhook*                                 | \Rennokki\Chargeswarm\Events\WebhookReceived::class                          |
| @handlePaymentFailed                            | \Rennokki\Chargeswarm\Events\PaymentFailed::class                            |
| @handlePaymentSucceeded*                        | \Rennokki\Chargeswarm\Events\PaymentSucceeded::class                         |
| @handleSubscriptionActivated                    | \Rennokki\Chargeswarm\Events\SubscriptionActivated::class                    |
| @handleSubscriptionCancellationReminder         | \Rennokki\Chargeswarm\Events\SubscriptionCancellationReminder::class         |
| @handleSubscriptionCancellationScheduled        | \Rennokki\Chargeswarm\Events\SubscriptionCancellationScheduled::class        |
| @handleSubscriptionCancelled*                   | \Rennokki\Chargeswarm\Events\SubscriptionCancelled::class                    |
| @handleSubscriptionChanged                      | \Rennokki\Chargeswarm\Events\SubscriptionChanged::class                      |
| @handleSubscriptionChangesScheduled             | \Rennokki\Chargeswarm\Events\SubscriptionChangesScheduled::class             |
| @handleSubscriptionCreated                      | \Rennokki\Chargeswarm\Events\SubscriptionCreated::class                      |
| @handleSubscriptionDeleted*                     | \Rennokki\Chargeswarm\Events\SubscriptionDeleted::class                      |
| @handleSubscriptionPaused                       | \Rennokki\Chargeswarm\Events\SubscriptionPaused::class                       |
| @handleSubscriptionPauseScheduled               | \Rennokki\Chargeswarm\Events\SubscriptionPauseScheduled::class               |
| @handleSubscriptionReactivated                  | \Rennokki\Chargeswarm\Events\SubscriptionReactivated::class                  |
| @handleSubscriptionRenewed*                     | \Rennokki\Chargeswarm\Events\SubscriptionRenewed::class                      |
| @handleSubscriptionResumed                      | \Rennokki\Chargeswarm\Events\SubscriptionResumed::class                      |
| @handleSubscriptionResumptionScheduled          | \Rennokki\Chargeswarm\Events\SubscriptionResumptionScheduled::class          |
| @handleSubscriptionScheduledCancellationRemoved | \Rennokki\Chargeswarm\Events\SubscriptionScheduledCancellationRemoved::class |
| @handleSubscriptionScheduledChangesRemoved      | \Rennokki\Chargeswarm\Events\SubscriptionScheduledChangesRemoved::class      |
| @handleSubscriptionScheduledResumptionRemoved   | \Rennokki\Chargeswarm\Events\SubscriptionScheduledResumptionRemoved::class   |
| @handleSubscriptionShippingAddressUpdated       | \Rennokki\Chargeswarm\Events\SubscriptionShippingAddressUpdated::class       |
| @handleSubscriptionStarted                      | \Rennokki\Chargeswarm\Events\SubscriptionStarted::class                      |
| @handleSubscriptionTrialEndReminder             | \Rennokki\Chargeswarm\Events\SubscriptionTrialEndReminder::class             |