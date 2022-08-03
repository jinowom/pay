<?php

namespace Jinowom\Pay\Contracts;

use Symfony\Component\HttpFoundation\Response;
use Jinowom\Supports\Collection;

interface GatewayInterface
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     *
     * @return Collection|Response
     */
    public function pay($endpoint, array $payload);
}
