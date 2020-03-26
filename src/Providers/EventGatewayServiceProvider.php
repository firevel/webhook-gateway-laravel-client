<?php

namespace Firevel\WebhookGatewayLaravelClient\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Route;
use Firevel\WebhookGatewayLaravelClient\Jobs\BroadcastEvent;

class WebhookGatewayClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/webhook-gateway.php', 'webhook-gateway');

        Route::post(config('webhook-gateway.client.route'), 'Firevel\WebhookGatewayLaravelClient\Http\Controllers\EventsController@handle');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/webhook-gateway.php' => config_path('webhook-gateway.php'),
        ], 'config');

        // Listen for events inside application and send them to Gateway.
        foreach (config('webhook-gateway.channels', []) as $channel => $events) {
            Event::listen($events, function ($payload) use ($channel) {
                if (method_exists($payload, 'toEventArray')) {
                    $data = $payload->toEventArray();
                } elseif (method_exists($payload, 'toArray')) {
                    $data = $payload->toArray();
                } elseif (is_array($payload)) {
                    $data = $payload;
                } else {
                    $data = (array) $payload;
                }

                if (method_exists($payload, 'eventMetadata')) {
                    $meta = (array) $payload->eventMetadata();
                } else {
                    $meta = [];
                }

                BroadcastEvent::dispatch($channel, $data, $meta)
                    ->onQueue(config('webhook-gateway.queue_name'))
                    ->onConnection(config('webhook-gateway.queue_connection'));
            });
        }
    }
}
