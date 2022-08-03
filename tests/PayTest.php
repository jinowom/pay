<?php

namespace Jinowom\Pay\Tests;

use Jinowom\Pay\Contracts\GatewayApplicationInterface;
use Jinowom\Pay\Exceptions\InvalidGatewayException;
use Jinowom\Pay\Gateways\Alipay;
use Jinowom\Pay\Gateways\Wechat;
use Jinowom\Pay\Pay;

class PayTest extends TestCase
{
    public function testAlipayGateway()
    {
        $alipay = Pay::alipay(['foo' => 'bar']);

        $this->assertInstanceOf(Alipay::class, $alipay);
        $this->assertInstanceOf(GatewayApplicationInterface::class, $alipay);
    }

    public function testWechatGateway()
    {
        $wechat = Pay::wechat(['foo' => 'bar']);

        $this->assertInstanceOf(Wechat::class, $wechat);
        $this->assertInstanceOf(GatewayApplicationInterface::class, $wechat);
    }

    public function testFooGateway()
    {
        $this->expectException(InvalidGatewayException::class);
        $this->expectExceptionMessage('INVALID_GATEWAY: Gateway [foo] Not Exists');

        Pay::foo([]);
    }
}
