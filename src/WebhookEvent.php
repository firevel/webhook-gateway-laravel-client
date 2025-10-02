<?php

namespace Firevel\WebhookGatewayLaravelClient;

use Firevel\WebhookGatewayLaravelClient\Http\Requests\EventRequest;

class WebhookEvent
{
    /**
     * Channel name.
     *
     * @var string
     */
    public $channel;

    /**
     * Meta data.
     *
     * @var array|null
     */
    public $meta;

    /**
     * Event unique id.
     *
     * @var string
     */
    public $id;

    /**
     * Event data.
     *
     * @var array
     */
    public $data;

    /**
     * Subscription data.
     *
     * @var array
     */
    public $subscription;

    /**
     * Create a new event instance.
     *
     * @param EventRequest|null $request
     *
     * @return void
     */
    public function __construct(?EventRequest $request = null)
    {
        if (!empty($request)) {
            $this->setChannel($request->input('channel.name'));
            $this->setMeta($request->input('meta'), []);
            $this->setId($request->input('id'), []);
            $this->setData($request->input('data'));
            $this->setSubscription($request->input('subscription'));
        }
    }

    /**
     * Make new Webhook Event instance.
     *
     * @return self
     */
    public static function make()
    {
        return new static();
    }

    /**
     * Get channel name.
     *
     * Example: billing.invoice.created
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set channel name.
     *
     * @param string $channel
     *
     * @return self
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get complete event meta or specific key using "dot" notation.
     *
     * @param string|null $key
     *
     * @return array|null
     */
    public function getMeta($key = null)
    {
        if (!empty($key)) {
            return data_get($this->meta, $key);
        }

        return $this->meta;
    }

    /**
     * Set event meta data.
     *
     * @param array|null $meta
     *
     * @return self
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get event id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set event id.
     *
     * @param string $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get complete event data or specific key using "dot" notation.
     *
     * @param string|null $key
     *
     * @return array
     */
    public function getData($key = null)
    {
        if (!empty($key)) {
            return data_get($this->data, $key);
        }

        return $this->data;
    }

    /**
     * Set event data.
     *
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get subscription data.
     *
     * @return array
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Set subscription data.
     *
     * @param array $subscription
     *
     * @return self
     */
    public function setSubscription(array $subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }
}
