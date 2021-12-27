<?php

namespace Firevel\WebhookGatewayLaravelClient\Providers;

use Firevel\WebhookGatewayLaravelClient\Jobs\BroadcastEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Route;

class WebhookGatewayClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/webhookgateway.php', 'webhookgateway');

        Route::post(config('webhookgateway.client.route'), 'Firevel\WebhookGatewayLaravelClient\Http\Controllers\EventsController@handle');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/webhookgateway.php' => config_path('webhookgateway.php'),
        ], 'config');

        // Listen for events inside application and send them to Gateway.
        foreach (config('webhookgateway.channels', []) as $channel => $events) {
            Event::listen($events, function ($payload, $payloads = null) use ($channel) {
                if (!($payloads === null)) { // Channels with wildcard.
                    $payload = $payloads[0];
                }
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
                    ->onQueue(config('webhookgateway.queue_name'))
                    ->onConnection(config('webhookgateway.queue_connection'));
            });
        }
    }
}
