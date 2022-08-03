<?php

namespace Jinowom\Pay\Gateways\Wechat;

use Jinowom\Pay\Exceptions\GatewayException;
use Jinowom\Pay\Exceptions\InvalidArgumentException;
use Jinowom\Pay\Exceptions\InvalidSignException;
use Jinowom\Pay\Gateways\Wechat;
use Jinowom\Supports\Collection;

class MiniappGateway extends MpGateway
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['appid'] = Support::getInstance()->miniapp_id;

        if (Wechat::MODE_SERVICE === $this->mode) {
            $payload['sub_appid'] = Support::getInstance()->sub_miniapp_id;
            $this->payRequestUseSubAppId = true;
        }

        return parent::pay($endpoint, $payload);
    }
}
