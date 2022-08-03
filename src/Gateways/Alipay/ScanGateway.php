<?php

namespace Jinowom\Pay\Gateways\Alipay;

use Jinowom\Pay\Events;
use Jinowom\Pay\Exceptions\GatewayException;
use Jinowom\Pay\Exceptions\InvalidArgumentException;
use Jinowom\Pay\Exceptions\InvalidConfigException;
use Jinowom\Pay\Exceptions\InvalidSignException;
use Jinowom\Pay\Gateways\Alipay;
use Jinowom\Supports\Collection;

class ScanGateway extends Gateway
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
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['method'] = 'alipay.trade.precreate';
        $biz_array = json_decode($payload['biz_content'], true);
        if ((Alipay::MODE_SERVICE === $this->mode) && (!empty(Support::getInstance()->pid))) {
            $biz_array['extend_params'] = is_array($biz_array['extend_params']) ? array_merge(['sys_service_provider_id' => Support::getInstance()->pid], $biz_array['extend_params']) : ['sys_service_provider_id' => Support::getInstance()->pid];
        }
        $payload['biz_content'] = json_encode(array_merge($biz_array, ['product_code' => '']));
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Alipay', 'Scan', $endpoint, $payload));

        return Support::requestApi($payload);
    }
}