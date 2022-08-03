<?php

namespace Jinowom\Pay\Contracts;

use Symfony\Component\HttpFoundation\Response;
use Jinowom\Supports\Collection;

interface GatewayInterface
{
    /**
     * Pay an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $endpoint
     *
     * @return Collection|Response
     */
    public function pay($endpoint, array $payload);
}
