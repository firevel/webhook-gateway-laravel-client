<?php

namespace Firevel\WebhookGatewayLaravelClient\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Firevel\WebhookGatewayLaravelClient\Http\Requests\EventRequest;

class EventsController extends Controller
{
	/**
	 * Handle event from event gateway.
	 *
	 * @param EventRequest $request
	 * @return void
	 */
    public function handle(EventRequest $request)
    {
    	event($request->input('channel.name'), [$request->input('data')]);
    }
}
