<?php

namespace Jinowom\Pay\Gateways\Alipay;

use Jinowom\Pay\Contracts\GatewayInterface;
use Jinowom\Pay\Events;
use Jinowom\Pay\Exceptions\GatewayException;
use Jinowom\Pay\Exceptions\InvalidConfigException;
use Jinowom\Pay\Exceptions\InvalidSignException;
use Jinowom\Supports\Collection;

class TransferGateway implements GatewayInterface
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['method'] = 'alipay.fund.trans.uni.transfer';
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Alipay', 'Transfer', $endpoint, $payload));

        return Support::requestApi($payload);
    }

    /**
     * Find.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param $order
     */
    public function find($order): array
    {
        return [
            'method' => 'alipay.fund.trans.order.query',
            'biz_content' => json_encode(is_array($order) ? $order : ['out_biz_no' => $order]),
        ];
    }
}
