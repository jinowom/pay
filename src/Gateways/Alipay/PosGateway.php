<?php

namespace Jinowom\Pay\Gateways\Alipay;

use Jinowom\Pay\Events;
use Jinowom\Pay\Exceptions\GatewayException;
use Jinowom\Pay\Exceptions\InvalidArgumentException;
use Jinowom\Pay\Exceptions\InvalidConfigException;
use Jinowom\Pay\Exceptions\InvalidSignException;
use Jinowom\Pay\Gateways\Alipay;
use Jinowom\Supports\Collection;

class PosGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $endpoint
     *
     * @throws InvalidArgumentException
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['method'] = 'alipay.trade.pay';
        $biz_array = json_decode($payload['biz_content'], true);
        if ((Alipay::MODE_SERVICE === $this->mode) && (!empty(Support::getInstance()->pid))) {
            $biz_array['extend_params'] = is_array($biz_array['extend_params']) ? array_merge(['sys_service_provider_id' => Support::getInstance()->pid], $biz_array['extend_params']) : ['sys_service_provider_id' => Support::getInstance()->pid];
        }
        $payload['biz_content'] = json_encode(array_merge(
            $biz_array,
            [
                'product_code' => 'FACE_TO_FACE_PAYMENT',
                'scene' => 'bar_code',
            ]
        ));
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Alipay', 'Pos', $endpoint, $payload));

        return Support::requestApi($payload);
    }
}
