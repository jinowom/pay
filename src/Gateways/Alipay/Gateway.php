<?php

namespace Jinowom\Pay\Gateways\Alipay;

use Jinowom\Pay\Contracts\GatewayInterface;
use Jinowom\Pay\Exceptions\InvalidArgumentException;
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
}
