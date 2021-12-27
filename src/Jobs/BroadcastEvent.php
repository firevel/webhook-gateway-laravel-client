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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
     * @param string $name    Event name
     * @param array  $payload Event payload
     *
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
            'service' => config('webhookgateway.service.name'),
            'channel' => ['name' => config('webhookgateway.service.name').'.'.$this->name],
            'data'    => $this->payload,
        ];

        if (!empty($this->meta)) {
            $data['meta'] = $this->meta;
        }

        $client->post(
            rtrim(config('webhookgateway.api'), '/').'/events',
            [
                'headers' => [
                    'x-signature' => hash_hmac(
                        config('webhookgateway.algorithm'),
                        json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        config('webhookgateway.service.secret')
                    ),
                ],
                \GuzzleHttp\RequestOptions::JSON => $data,
            ]
        );
    }
}
