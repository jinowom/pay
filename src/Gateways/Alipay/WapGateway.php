<?php

namespace Jinowom\Pay\Gateways\Alipay;

class WapGateway extends WebGateway
{
    /**
     * Get method config.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     */
    protected function getMethod(): string
    {
        return 'alipay.trade.wap.pay';
    }

    /**
     * Get productCode config.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     */
    protected function getProductCode(): string
    {
        return 'QUICK_WAP_WAY';
    }
}
