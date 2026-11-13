<?php

namespace Firevel\WebhookGatewayLaravelClient\Http\Controllers;

use Firevel\WebhookGatewayLaravelClient\Http\Requests\EventRequest;
use Firevel\WebhookGatewayLaravelClient\Jobs\HandleEvent;
use Illuminate\Routing\Controller;

class EventsController extends Controller
{
    /**
     * Handle event from event gateway.
     *
     * @param EventRequest $request
     *
     * @return void
     */
    public function handle(EventRequest $request)
    {
        $eventClass = config('webhookgateway.event_class');
        $channel = $request->input('channel.name');
        $event = new $eventClass($request);

        if (!config('webhookgateway.client.queue')) {
            event($channel, $event);

            return;
        }

        HandleEvent::dispatch($channel, $event)
            ->onQueue(config('webhookgateway.queue_name'))
            ->onConnection(config('webhookgateway.queue_connection'));
    }
}
