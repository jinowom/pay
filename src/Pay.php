<?php

namespace Jinowom\Pay;

use Exception;
use Jinowom\Pay\Contracts\GatewayApplicationInterface;
use Jinowom\Pay\Exceptions\InvalidGatewayException;
use Jinowom\Pay\Gateways\Alipay;
use Jinowom\Pay\Gateways\Wechat;
use Jinowom\Pay\Listeners\KernelLogSubscriber;
use Jinowom\Supports\Config;
use Jinowom\Supports\Log;
use Jinowom\Supports\Logger;
use Jinowom\Supports\Str;

/**
 * @method static Alipay alipay(array $config) 支付宝
 * @method static Wechat wechat(array $config) 微信
 */
class Pay
{
    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Bootstrap.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @throws Exception
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);

        $this->registerLogService();
        $this->registerEventService();
    }

    /**
     * Magic static call.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $method
     * @param array  $params
     *
     * @throws InvalidGatewayException
     * @throws Exception
     */
    public static function __callStatic($method, $params): GatewayApplicationInterface
    {
        $app = new self(...$params);

        return $app->create($method);
    }

    /**
     * Create a instance.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $method
     *
     * @throws InvalidGatewayException
     */
    protected function create($method): GatewayApplicationInterface
    {
        $gateway = __NAMESPACE__.'\\Gateways\\'.Str::studly($method);

        if (class_exists($gateway)) {
            return self::make($gateway);
        }

        throw new InvalidGatewayException("Gateway [{$method}] Not Exists");
    }

    /**
     * Make a gateway.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string $gateway
     *
     * @throws InvalidGatewayException
     */
    protected function make($gateway): GatewayApplicationInterface
    {
        $app = new $gateway($this->config);

        if ($app instanceof GatewayApplicationInterface) {
            return $app;
        }

        throw new InvalidGatewayException("Gateway [{$gateway}] Must Be An Instance Of GatewayApplicationInterface");
    }

    /**
     * Register log service.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @throws Exception
     */
    protected function registerLogService()
    {
        $config = $this->config->get('log');
        $config['identify'] = 'yansongda.pay';

        $logger = new Logger();
        $logger->setConfig($config);

        Log::setInstance($logger);
    }

    /**
     * Register event service.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     */
    protected function registerEventService()
    {
        Events::setDispatcher(Events::createDispatcher());

        Events::addSubscriber(new KernelLogSubscriber());
    }
}
