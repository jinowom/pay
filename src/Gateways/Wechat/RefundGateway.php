<?php

namespace Jinowom\Pay\Gateways\Wechat;

use Jinowom\Pay\Exceptions\InvalidArgumentException;

class RefundGateway extends Gateway
{
    /**
     * Find.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param $order
     */
    public function find($order): array
    {
        return [
            'endpoint' => 'pay/refundquery',
            'order' => is_array($order) ? $order : ['out_trade_no' => $order],
            'cert' => false,
        ];
    }

    /**
     * Pay an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $endpoint
     *
     * @throws InvalidArgumentException
     */
    public function pay($endpoint, array $payload)
    {
        throw new InvalidArgumentException('Not Support Refund In Pay');
    }

    /**
     * Get trade type config.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @throws InvalidArgumentException
     */
    protected function getTradeType()
    {
        throw new InvalidArgumentException('Not Support Refund In Pay');
    }
}
