<?php

namespace Jinowom\Pay\Contracts;

use Symfony\Component\HttpFoundation\Response;
use Jinowom\Supports\Collection;

interface GatewayApplicationInterface
{
    /**
     * To pay.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string $gateway
     * @param array  $params
     *
     * @return Collection|Response
     */
    public function pay($gateway, $params);

    /**
     * Query an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string|array $order
     *
     * @return Collection
     */
    public function find($order, string $type);

    /**
     * Refund an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @return Collection
     */
    public function refund(array $order);

    /**
     * Cancel an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string|array $order
     *
     * @return Collection
     */
    public function cancel($order);

    /**
     * Close an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string|array $order
     *
     * @return Collection
     */
    public function close($order);

    /**
     * Verify a request.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string|array|null $content
     *
     * @return Collection
     */
    public function verify($content, bool $refund);

    /**
     * Echo success to server.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @return Response
     */
    public function success();
}
