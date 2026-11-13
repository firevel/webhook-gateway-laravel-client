<?php

namespace Firevel\WebhookGatewayLaravelClient\Tests;

use Firevel\WebhookGatewayLaravelClient\Jobs\BroadcastEvent;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;

class ShareEventsTest extends TestCase
{
    protected function channels()
    {
        return [
            'user.suspended'  => [UserSuspended::class],
            'invoice.created' => ['invoice.created'],
            'model.saved'     => ['eloquent.saved: *'],
        ];
    }

    /**
     * Run the job against a fake gateway and return the request it sent.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function send(BroadcastEvent $job)
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200)]));
        $stack->push(Middleware::history($history));
        $this->app->instance(Client::class, new Client(['handler' => $stack]));

        $this->app->call([$job, 'handle']);

        return $history[0]['request'];
    }

    public function test_event_object_is_shared_with_custom_data_and_meta()
    {
        Queue::fake();

        event(new UserSuspended());

        Queue::assertPushedOn('webhooks', BroadcastEvent::class, function (BroadcastEvent $job) {
            return $job->name === 'user.suspended'
                && $job->payload === ['id' => 5]
                && $job->meta === ['role' => ['id' => 7]]
                && $job->connection === 'sync';
        });
    }

    public function test_array_payload_is_shared_as_is()
    {
        Queue::fake();

        event('invoice.created', [['id' => 15]]);

        Queue::assertPushed(BroadcastEvent::class, function (BroadcastEvent $job) {
            return $job->name === 'invoice.created' && $job->payload === ['id' => 15] && $job->meta === [];
        });
    }

    public function test_wildcard_events_share_the_first_payload()
    {
        Queue::fake();

        event('eloquent.saved: App\Models\User', [new UserSuspended()]);

        Queue::assertPushed(BroadcastEvent::class, function (BroadcastEvent $job) {
            return $job->name === 'model.saved' && $job->payload === ['id' => 5];
        });
    }

    public function test_events_outside_the_channels_config_are_not_shared()
    {
        Queue::fake();

        event('invoice.deleted', [['id' => 15]]);

        Queue::assertNothingPushed();
    }

    public function test_event_is_posted_to_the_gateway()
    {
        $request = $this->send(new BroadcastEvent('invoice.created', ['id' => 15], ['role' => ['id' => 7]]));

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://gateway.test/webhook/events', (string) $request->getUri());
        $this->assertSame('application/json', $request->getHeaderLine('content-type'));
        $this->assertSame([
            'service' => 'billing',
            'channel' => ['name' => 'billing.invoice.created'],
            'data'    => ['id' => 15],
            'meta'    => ['role' => ['id' => 7]],
        ], json_decode((string) $request->getBody(), true));
    }

    public static function fixtureProvider()
    {
        $cases = [];

        foreach (self::fixtures() as $fixture) {
            $cases[$fixture['name']] = [$fixture];
        }

        return $cases;
    }

    /**
     * The gateway only accepts an event when the signature matches the body it receives.
     */
    #[DataProvider('fixtureProvider')]
    public function test_request_matches_the_shared_signature_fixture(array $fixture)
    {
        $expected = json_decode($fixture['body'], true);
        config(['webhookgateway.service.secret' => $fixture['secret']]);

        $request = $this->send(new BroadcastEvent(
            substr($expected['channel']['name'], strlen('billing.')),
            $expected['data'],
            $expected['meta'] ?? []
        ));

        $body = (string) $request->getBody();
        $this->assertSame($fixture['signature'], $request->getHeaderLine('x-signature'));
        $this->assertSame($fixture['signature'], hash_hmac('sha256', $body, $fixture['secret']));
        $this->assertSame($fixture['body'], $body);
    }
}

class UserSuspended
{
    public function toEventArray()
    {
        return ['id' => 5];
    }

    public function eventMetadata()
    {
        return ['role' => ['id' => 7]];
    }
}
