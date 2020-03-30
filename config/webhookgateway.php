<?php

return [
    // Webhook gateway endpoint.
    'api' => env('WEBHOOKGATEWAY_API'),
    
    // Algorithm used for signatures (HMAC). Must be equal to algorithm used on gateway.
    'algorithm' => 'sha256',

    /*
    |--------------------------------------------------------------------------
    | Client credentials.
    |--------------------------------------------------------------------------
    |
    | Client account is used to listen for events happening on Gateway.
    |
    | By default events are sent by POST to /events endpoint.
    |
     */
    'client' => [
        'secret' => env('WEBHOOKGATEWAY_CLIENT_SECRET'),
        'route' => env('WEBHOOKGATEWAY_CLIENT_ROUTE', 'events'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service credentials.
    |--------------------------------------------------------------------------
    |
    | Service credentials are used to share events with Gateway.
    |
     */
    'service' => [
        'name' => env('WEBHOOKGATEWAY_SERVICE_NAME'),
        'secret' => env('WEBHOOKGATEWAY_SERVICE_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue name.
    |--------------------------------------------------------------------------
    |
    | Events service using queue. Here you can setup queue name.
    |
     */
    'queue_name' => env('WEBHOOKGATEWAY_QUEUE_NAME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Queue connection used for jobs.
    |
    */
    'queue_connection' => env('WEBHOOKGATEWAY_QUEUE_CONNECTION', config('queue.default')),

    /*
    |--------------------------------------------------------------------------
    | Events shared with gateway.
    |--------------------------------------------------------------------------
    |
    | List of events to share with gateway, for example:
    | 'user.saved' => [
    |     'eloquent.saved: App/User',
    | ]
    |
     */
    'channels' => [],

    /*
    |--------------------------------------------------------------------------
    | Event class.
    |--------------------------------------------------------------------------
    |
    | Event class used during event disptach on cleint side.
    |
     */
    'event_class' => Firevel\WebhookGatewayLaravelClient\WebhookEvent::class,

];
