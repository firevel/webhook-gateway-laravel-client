<?php

namespace Firevel\WebhookGatewayLaravelClient\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    /**
     * Validate signature.
     *
     * @return bool
     */
    public function authorize()
    {
        $signature = $this->header('x-signature');

        if (empty($signature)) {
            return false;
        }

        if (hash_equals(hash_hmac(config('webhookgateway.algorithm'), $this->getContent(), config('webhookgateway.client.secret')), $signature)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'           => 'required|string',
            'data'         => 'required|array',
            'channel.name' => 'required|string',
        ];
    }
}
