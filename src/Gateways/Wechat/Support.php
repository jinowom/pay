<?php

namespace Jinowom\Pay\Gateways\Wechat;

use Exception;
use Jinowom\Pay\Events;
use Jinowom\Pay\Exceptions\BusinessException;
use Jinowom\Pay\Exceptions\GatewayException;
use Jinowom\Pay\Exceptions\InvalidArgumentException;
use Jinowom\Pay\Exceptions\InvalidSignException;
use Jinowom\Pay\Gateways\Wechat;
use Jinowom\Pay\Log;
use Jinowom\Supports\Collection;
use Jinowom\Supports\Config;
use Jinowom\Supports\Str;
use Jinowom\Supports\Traits\HasHttpRequest;

/**
 * @author cocoli6000 <cocoli6000@gmail.com>
 *
 * @property string appid
 * @property string app_id
 * @property string miniapp_id
 * @property string sub_appid
 * @property string sub_app_id
 * @property string sub_miniapp_id
 * @property string mch_id
 * @property string sub_mch_id
 * @property string key
 * @property string return_url
 * @property string cert_client
 * @property string cert_key
 * @property array log
 * @property array http
 * @property string mode
 */
class Support
{
    use HasHttpRequest;

    /**
     * Wechat gateway.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Instance.
     *
     * @var Support
     */
    private static $instance;

    /**
     * Bootstrap.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     */
    private function __construct(Config $config)
    {
        $this->baseUri = Wechat::URL[$config->get('mode', Wechat::MODE_NORMAL)];
        $this->config = $config;

        $this->setHttpOptions();
    }

    /**
     * __get.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param $key
     *
     * @return mixed|Config|null
     */
    public function __get($key)
    {
        return $this->getConfig($key);
    }

    /**
     * create.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     *
     * @return Support
     */
    public static function create(Config $config)
    {
        if ('cli' === php_sapi_name() || !(self::$instance instanceof self)) {
            self::$instance = new self($config);

            self::setDevKey();
        }

        return self::$instance;
    }

    /**
     * getInstance.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @throws InvalidArgumentException
     *
     * @return Support
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            throw new InvalidArgumentException('You Should [Create] First Before Using');
        }

        return self::$instance;
    }

    /**
     * clear.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     */
    public static function clear()
    {
        self::$instance = null;
    }

    /**
     * Request wechat api.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $endpoint
     * @param array  $data
     * @param bool   $cert
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     */
    public static function requestApi($endpoint, $data, $cert = false): Collection
    {
        Events::dispatch(new Events\ApiRequesting('Wechat', '', self::$instance->getBaseUri().$endpoint, $data));

        //xmlData?????????headers?????????????????????????????????????????????
        $options = [
            'headers' => [
                'Content-Type' => 'application/xml',
            ],
        ];

        $certOptions = $cert ? [
            'cert' => self::$instance->cert_client,
            'ssl_key' => self::$instance->cert_key,
        ] : [];

        $result = self::$instance->post(
            $endpoint,
            self::toXml($data),
            array_merge($options, $certOptions)
        );
        $result = is_array($result) ? $result : self::fromXml($result);

        Events::dispatch(new Events\ApiRequested('Wechat', '', self::$instance->getBaseUri().$endpoint, $result));

        return self::processingApiResult($endpoint, $result);
    }

    /**
     * Filter payload.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param array        $payload
     * @param array|string $params
     * @param bool         $preserve_notify_url
     *
     * @throws InvalidArgumentException
     */
    public static function filterPayload($payload, $params, $preserve_notify_url = false): array
    {
        $type = self::getTypeName($params['type'] ?? '');

        $payload = array_merge(
            $payload,
            is_array($params) ? $params : ['out_trade_no' => $params]
        );
        $payload['appid'] = self::$instance->getConfig($type, '');

        if (Wechat::MODE_SERVICE === self::$instance->getConfig('mode', Wechat::MODE_NORMAL)) {
            $payload['sub_appid'] = self::$instance->getConfig('sub_'.$type, '');
        }

        unset($payload['trade_type'], $payload['type']);
        if (!$preserve_notify_url) {
            unset($payload['notify_url']);
        }

        $payload['sign'] = self::generateSign($payload);

        return $payload;
    }

    /**
     * Generate wechat sign.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param array $data
     *
     * @throws InvalidArgumentException
     */
    public static function generateSign($data): string
    {
        $key = self::$instance->key;

        if (is_null($key)) {
            throw new InvalidArgumentException('Missing Wechat Config -- [key]');
        }

        ksort($data);

        $string = md5(self::getSignContent($data).'&key='.$key);

        Log::debug('Wechat Generate Sign Before UPPER', [$data, $string]);

        return strtoupper($string);
    }

    /**
     * Generate sign content.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param array $data
     */
    public static function getSignContent($data): string
    {
        $buff = '';

        foreach ($data as $k => $v) {
            $buff .= ('sign' != $k && '' != $v && !is_array($v)) ? $k.'='.$v.'&' : '';
        }

        Log::debug('Wechat Generate Sign Content Before Trim', [$data, $buff]);

        return trim($buff, '&');
    }

    /**
     * Decrypt refund contents.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $contents
     */
    public static function decryptRefundContents($contents): string
    {
        return openssl_decrypt(
            base64_decode($contents),
            'AES-256-ECB',
            md5(self::$instance->key),
            OPENSSL_RAW_DATA
        );
    }

    /**
     * Convert array to xml.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param array $data
     *
     * @throws InvalidArgumentException
     */
    public static function toXml($data): string
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new InvalidArgumentException('Convert To Xml Error! Invalid Array!');
        }

        $xml = '<xml>';
        foreach ($data as $key => $val) {
            $xml .= is_numeric($val) ? '<'.$key.'>'.$val.'</'.$key.'>' :
                                       '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
        }
        $xml .= '</xml>';

        return $xml;
    }

    /**
     * Convert xml to array.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $xml
     *
     * @throws InvalidArgumentException
     */
    public static function fromXml($xml): array
    {
        if (!$xml) {
            throw new InvalidArgumentException('Convert To Array Error! Invalid Xml!');
        }

        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(true);
        }

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * Get service config.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return mixed|null
     */
    public function getConfig($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config->all();
        }

        if ($this->config->has($key)) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Get app id according to param type.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param string $type
     */
    public static function getTypeName($type = ''): string
    {
        switch ($type) {
            case '':
                $type = 'app_id';
                break;
            case 'app':
                $type = 'appid';
                break;
            default:
                $type = $type.'_id';
        }

        return $type;
    }

    /**
     * Get Base Uri.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * processingApiResult.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @param $endpoint
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     *
     * @return Collection
     */
    protected static function processingApiResult($endpoint, array $result)
    {
        if (!isset($result['return_code']) || 'SUCCESS' != $result['return_code']) {
            throw new GatewayException('Get Wechat API Error:'.($result['return_msg'] ?? $result['retmsg'] ?? ''), $result);
        }

        if (isset($result['result_code']) && 'SUCCESS' != $result['result_code']) {
            throw new BusinessException('Wechat Business Error: '.$result['err_code'].' - '.$result['err_code_des'], $result);
        }

        if ('pay/getsignkey' === $endpoint ||
            false !== strpos($endpoint, 'mmpaymkttransfers') ||
            self::generateSign($result) === $result['sign']) {
            return new Collection($result);
        }

        Events::dispatch(new Events\SignFailed('Wechat', '', $result));

        throw new InvalidSignException('Wechat Sign Verify FAILED', $result);
    }

    /**
     * setDevKey.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     * @throws Exception
     *
     * @return Support
     */
    private static function setDevKey()
    {
        if (Wechat::MODE_DEV == self::$instance->mode) {
            $data = [
                'mch_id' => self::$instance->mch_id,
                'nonce_str' => Str::random(),
            ];
            $data['sign'] = self::generateSign($data);

            $result = self::requestApi('pay/getsignkey', $data);

            self::$instance->config->set('key', $result['sandbox_signkey']);
        }

        return self::$instance;
    }

    /**
     * Set Http options.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     */
    private function setHttpOptions(): self
    {
        if ($this->config->has('http') && is_array($this->config->get('http'))) {
            $this->config->forget('http.base_uri');
            $this->httpOptions = $this->config->get('http');
        }

        return $this;
    }
}
