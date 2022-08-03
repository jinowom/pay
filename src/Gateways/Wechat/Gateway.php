<?php

namespace Jinowom\Pay\Gateways\Wechat;

use Jinowom\Pay\Contracts\GatewayInterface;
use Jinowom\Pay\Events;
use Jinowom\Pay\Exceptions\GatewayException;
use Jinowom\Pay\Exceptions\InvalidArgumentException;
use Jinowom\Pay\Exceptions\InvalidSignException;
use Jinowom\Supports\Collection;

abstract class Gateway implements GatewayInterface
{
    /**
     * Mode.
     *
     * @var string
     */
    protected $mode;

    /**
     * Bootstrap.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->mode = Support::getInstance()->mode;
    }

    /**
     * Pay an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $endpoint
     *
     * @return Collection
     */
    abstract public function pay($endpoint, array $payload);

    /**
     * Find.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string|array $order
     */
    public function find($order): array
    {
        return [
            'endpoint' => 'pay/orderquery',
            'order' => is_array($order) ? $order : ['out_trade_no' => $order],
            'cert' => false,
        ];
    }

    /**
     * Get trade type config.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @return string
     */
    abstract protected function getTradeType();

    /**
     * Schedule an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param array $payload
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     */
    protected function preOrder($payload): Collection
    {
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\MethodCalled('Wechat', 'PreOrder', '', $payload));

        return Support::requestApi('pay/unifiedorder', $payload);
    }
}
