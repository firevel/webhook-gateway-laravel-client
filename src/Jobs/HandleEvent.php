<?php

namespace Firevel\WebhookGatewayLaravelClient\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Channel name.
     *
     * @var string
     */
    public $channel;

    /**
     * Event.
     *
     * @var mixed
     */
    public $event;

    /**
     * Create a new job instance.
     *
     * @param string $channel Channel name
     * @param mixed  $event   Event
     *
     * @return void
     */
    public function __construct($channel, $event)
    {
        $this->channel = $channel;
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        event($this->channel, $this->event);
    }
}
