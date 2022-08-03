<?php

namespace Jinowom\Pay\Tests\Gateways;

use Symfony\Component\HttpFoundation\Response;
use Jinowom\Pay\Pay;
use Jinowom\Pay\Tests\TestCase;

class AlipayTest extends TestCase
{
    public function testSuccess()
    {
        $success = Pay::alipay([])->success();

        $this->assertInstanceOf(Response::class, $success);
        $this->assertEquals('success', $success->getContent());
    }
}
