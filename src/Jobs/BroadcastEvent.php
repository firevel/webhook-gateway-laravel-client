<?php

namespace Firevel\WebhookGatewayLaravelClient\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Event payload.
     *
     * @var array
     */
    public $payload;

    /**
     * Event name.
     *
     * @var string
     */
    public $name;

    /**
     * Meta data.
     *
     * @var array
     */
    public $meta;

    /**
     * Create a new job instance.
     *
     * @param string $name Event name
     * @param array $payload Event payload
     * @return void
     */
    public function __construct($name, array $payload, array $meta = [])
    {
        $this->name = $name;
        $this->payload = $payload;
        $this->meta = $meta;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();

        $data = [
            'service' => config('webhook-gateway.service.name'),
            'channel' => ['name' => config('webhook-gateway.service.name').'.'.$this->name],
            'data' => $this->payload,
        ];

        if (!empty($this->meta)) {
            $data['meta'] = $this->meta;
        }

        $client->post(
            config('webhook-gateway.api'),
            [
                'headers' => [
                    'x-signature' => hash_hmac(
                        config('webhook-gateway.algorithm'),
                        json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
                        config('webhook-gateway.service.secret')
                    )
                ],
                \GuzzleHttp\RequestOptions::JSON => $data,
            ]
        );
    }
}
