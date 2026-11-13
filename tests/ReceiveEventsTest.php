<?php

namespace Firevel\WebhookGatewayLaravelClient\Tests;

use Firevel\WebhookGatewayLaravelClient\Jobs\HandleEvent;
use Firevel\WebhookGatewayLaravelClient\WebhookEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

class ReceiveEventsTest extends TestCase
{
    /**
     * Call as sent by the gateway.
     *
     * @var array
     */
    protected $call = [
        'id'           => '7c0a9b0e-5a56-4a55-8f0b-0a4f1f1f3c11',
        'channel'      => ['name' => 'billing.invoice.created'],
        'data'         => ['id' => 15, 'user' => ['email' => 'jane@example.com']],
        'meta'         => ['role' => ['id' => 7]],
        'subscription' => [
            'channel' => 'billing.invoice.created',
            'client'  => ['id' => 'client-1'],
        ],
        'delivery_attempt' => 1,
    ];

    /**
     * Events received by listeners.
     *
     * @var array
     */
    protected $received = [];

    protected function setUp(): void
    {
        parent::setUp();

        Event::listen('billing.invoice.created', function ($event) {
            $this->received[] = $event;
        });
    }

    protected function postCall($body, $signature = null)
    {
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];

        if ($signature !== null) {
            $server['HTTP_X_SIGNATURE'] = $signature;
        }

        return $this->call('POST', '/events', [], [], [], $server, $body);
    }

    protected function sign($body, $secret = self::CLIENT_SECRET)
    {
        return hash_hmac('sha256', $body, $secret);
    }

    public function test_signed_call_is_dispatched_as_laravel_event()
    {
        // Same serialization as the gateway: JSON.stringify output.
        $body = json_encode($this->call, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->postCall($body, $this->sign($body))->assertStatus(200);

        $this->assertCount(1, $this->received);
        $event = $this->received[0];
        $this->assertInstanceOf(WebhookEvent::class, $event);
        $this->assertSame('7c0a9b0e-5a56-4a55-8f0b-0a4f1f1f3c11', $event->getId());
        $this->assertSame('billing.invoice.created', $event->getChannel());
        $this->assertSame($this->call['data'], $event->getData());
        $this->assertSame('jane@example.com', $event->getData('user.email'));
        $this->assertSame($this->call['meta'], $event->getMeta());
        $this->assertSame(7, $event->getMeta('role.id'));
        $this->assertSame($this->call['subscription'], $event->getSubscription());
    }

    public function test_call_with_wrong_signature_is_rejected()
    {
        $body = json_encode($this->call);

        $this->postCall($body, $this->sign($body, 'other-secret'))->assertStatus(403);
        $this->postCall($body.' ', $this->sign($body))->assertStatus(403);

        $this->assertCount(0, $this->received);
    }

    public function test_call_without_signature_is_rejected()
    {
        $this->postCall(json_encode($this->call))->assertStatus(403);

        $this->assertCount(0, $this->received);
    }

    public function test_call_without_id_is_invalid()
    {
        $call = $this->call;
        unset($call['id']);
        $body = json_encode($call);

        $this->postCall($body, $this->sign($body))->assertStatus(422);

        $this->assertCount(0, $this->received);
    }

    public function test_call_can_be_handled_on_a_queue()
    {
        config(['webhookgateway.client.queue' => true]);
        Queue::fake();
        $body = json_encode($this->call);

        $this->postCall($body, $this->sign($body))->assertStatus(200);

        $this->assertCount(0, $this->received);
        Queue::assertPushedOn('webhooks', HandleEvent::class, function (HandleEvent $job) {
            return $job->channel === 'billing.invoice.created' && $job->connection === 'sync';
        });
    }

    public function test_queued_job_dispatches_the_event()
    {
        $event = WebhookEvent::make()->setChannel('billing.invoice.created')->setData(['id' => 15]);

        // The job is serialized on a real queue, so the event must survive that.
        $job = unserialize(serialize(new HandleEvent('billing.invoice.created', $event)));
        $job->handle();

        $this->assertCount(1, $this->received);
        $this->assertSame(['id' => 15], $this->received[0]->getData());
    }
}
