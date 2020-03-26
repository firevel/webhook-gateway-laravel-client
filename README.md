# Readme

# Webhook Gateway client for Laravel

This package is built to share events between micro-services though Webhook Gateway.

## Installation

Install using composer:

`composer require firevel/webhook-gateway-laravel-client`

Publish config:

`php artisan vendor:publish --provider="Firevel\WebhookGatewayLaravelClient\Providers\WebhookGatewayClientServiceProvider" --tag="config"`

# Client

Client is responsible for receiving events from Webhook Gateway and dispatching them internally.

## Setup

1. Create you client account on Webhook Gateway and set your client secret and client url (by default `https://HOST/events`).
2. Set your client secret in `config/webhook-gateway.php` or `WEBHOOKGATEWAY_CLIENT_SECRET` env variable.

## Usage

Set channels you would like to listen to in Webhook Gateway subscribers section. Events are always starting with service for example `billing.invoice.created`.

Example:

    Event::listen('billing.invoice.created', function ($invoice) {
        PaymentService::payInvoice($invoice);
    });

# Service

Service is responsible for sharing selected events with Webhook Gateway and other micro-services.

## Setup

1. Create a new service at Webhook Gateway and set service name with service secret.
2. Setup you Webhook Gateway, service name, and secret in `config/webhook-gateway.php`.
3. Setup events you would like to share with Webhook Gateway using `webhook-gateway.channels` config.

## Usage

### Sharing events

Events matching `webhook-gateway.channels` pattern (currently no wildcard support), are going to be shared with other micro services subscribed to namespace used in channels configuration. Webhook Gateway will automatically add service prefix to every event dispatched.
For example if you are using service name `billing` and setup channel

    'invoice.created' => [
        'eloquent.created: App/Models/Invoice',
     ]

every save event of invoice model going to be dispatched as `billing.invoice.created`.

### Eloquent events

By default, eloquent models are transformer to array using `(array) $model`. If you would like to customize event format add to your model:

```
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
```
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

### Credits

- SpringboardVR for initial implementation.
