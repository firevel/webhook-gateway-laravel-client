<?php

namespace Firevel\WebhookGatewayLaravelClient\Http\Controllers;

use Firevel\WebhookGatewayLaravelClient\Http\Requests\EventRequest;
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

        event($request->input('channel.name'), new $eventClass($request));
    }
}
