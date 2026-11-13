<?php

namespace Firevel\WebhookGatewayLaravelClient\Tests;

use Firevel\WebhookGatewayLaravelClient\Providers\WebhookGatewayClientServiceProvider;
use Firevel\WebhookGatewayLaravelClient\WebhookEvent;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    const CLIENT_SECRET = 'client-secret';

    protected function getPackageProviders($app)
    {
        return [WebhookGatewayClientServiceProvider::class];
    }

    /**
     * Channels shared with the gateway in this test.
     *
     * @return array
     */
    protected function channels()
    {
        return [];
    }

    protected function getEnvironmentSetUp($app)
    {
        // The provider reads this while registering, so it is set as a whole here.
        $app['config']->set('webhookgateway', [
            'api'       => 'https://gateway.test/webhook/',
            'algorithm' => 'sha256',
            'client'    => [
                'secret' => self::CLIENT_SECRET,
                'route'  => 'events',
                'queue'  => false,
            ],
            'service' => [
                'name'   => 'billing',
                'secret' => 'service-secret',
            ],
            'queue_name'       => 'webhooks',
            'queue_connection' => 'sync',
            'channels'         => $this->channels(),
            'event_class'      => WebhookEvent::class,
        ]);
    }

    /**
     * Shared signature fixtures, also used by express-webhook-gateway.
     *
     * @return array
     */
    protected static function fixtures()
    {
        return json_decode(file_get_contents(__DIR__.'/fixtures/signatures.json'), true)['cases'];
    }
}
