<?php

namespace Jinowom\Pay\Gateways\Wechat;

use Symfony\Component\HttpFoundation\Request;
use Jinowom\Pay\Events;
use Jinowom\Pay\Exceptions\GatewayException;
use Jinowom\Pay\Exceptions\InvalidArgumentException;
use Jinowom\Pay\Exceptions\InvalidSignException;
use Jinowom\Pay\Gateways\Wechat;
use Jinowom\Supports\Collection;

class RedpackGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $endpoint
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['wxappid'] = $payload['appid'];

        if ('cli' !== php_sapi_name()) {
            $payload['client_ip'] = Request::createFromGlobals()->server->get('SERVER_ADDR');
        }

        if (Wechat::MODE_SERVICE === $this->mode) {
            $payload['msgappid'] = $payload['appid'];
        }

        unset($payload['appid'], $payload['trade_type'],
              $payload['notify_url'], $payload['spbill_create_ip']);

        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Wechat', 'Redpack', $endpoint, $payload));

        return Support::requestApi(
            'mmpaymkttransfers/sendredpack',
            $payload,
            true
        );
    }

    /**
     * Find.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param $billno
     */
    public function find($billno): array
    {
        return [
            'endpoint' => 'mmpaymkttransfers/gethbinfo',
            'order' => ['mch_billno' => $billno, 'bill_type' => 'MCHT'],
            'cert' => true,
        ];
    }

    /**
     * Get trade type config.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     */
    protected function getTradeType(): string
    {
        return '';
    }
}
