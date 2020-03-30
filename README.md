# Readme

# Webhook Gateway client for Laravel

This package is built to share events between micro-services though Webhook Gateway.

## Installation

Install using composer:

```bash
composer require firevel/webhook-gateway-laravel-client
```

Publish config:

```bash
php artisan vendor:publish --provider="Firevel\WebhookGatewayLaravelClient\Providers\WebhookGatewayClientServiceProvider" --tag="config"
```

# Client

Client is responsible for receiving events from Webhook Gateway and dispatching them internally.

## Setup

1. Create you client account on Webhook Gateway and set your client secret and client url (by default `https://HOST/events`).
2. Set your client secret in `config/webhookgateway.php` or `WEBHOOKGATEWAY_CLIENT_SECRET` env variable.

## Usage

Set channels you would like to listen to in Webhook Gateway subscribers section. Events are always starting with service for example `billing.invoice.created`.

```php
Event::listen('billing.invoice.created', function ($invoice) {
    PaymentService::payInvoice($invoice);
});
```

You can also use Laravel event listeners.

```php
protected $listen = [
    'billing.invoice.created' => [
        'App\Listeners\InvoiceCreated',
    ],
];
```



Events are fired with `WebhookEvent` payload. It contains methods:

```php
$event->getData(); // Get event data array.
$event->getChannel(); // Get event channel name.
$event->getMeta(); // Get event meta data array.
$event->getId(); // Get event id.
$event->getSubscription(); Get event subscription array.
```

You can set custom webhook event class in `webhookgateway.event_class` configuration.

# Service

Service is responsible for sharing selected events with Webhook Gateway and other micro-services.

## Setup

1. Create a new service at Webhook Gateway and set service name with service secret.
2. Setup you Webhook Gateway, service name, and secret in `config/webhookgateway.php`.
3. Setup events you would like to share with Webhook Gateway using `webhookgateway.channels` config.

## Usage

### Sharing events

Events matching `webhookgateway.channels` pattern (currently no wildcard support), are going to be shared with other micro services subscribed to namespace used in channels configuration. Webhook Gateway will automatically add service prefix to every event dispatched.
For example if you are using service name `billing` and setup channel
```php
'invoice.created' => [
    'eloquent.created: App/Models/Invoice',
 ]
```
every save event of invoice model going to be dispatched as `billing.invoice.created`.

### Eloquent events

By default, eloquent models are transformer to array using `(array) $model`. If you would like to customize event format add to your model:

```php
/**
 * Get the event data array for the model.
 *
 * @return array
 */
public function toEventArray()
{
    // Your code here...
}
```

You can also attach meta data to each event by adding to your model:
```php
/**
 * Get the event meta data array.
 *
 * @return array
 */
public function eventMetadata()
{
    // Your code here...
}
```

### Laravel events

To share Laravel events with event gateway you can use regular `webhookgateway.channels` configuration for example:
```php
'user.suspended' => [
    'App\Events\UserSuspended'
],
```

You can use `toEventArray` and `eventMetadata` method to customize payload or meta data.

# FAQ

### Can I use WebHook gateway as Event Broadcasting driver?

You would need to build custom driver (check `Illuminate\Contracts\Broadcasting`). Broadcasting driver is not the part of this package as laravel broadcasting was developed for other purposes (web sockets).

### Credits

- SpringboardVR for initial implementation.
