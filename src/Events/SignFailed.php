<?php

namespace Jinowom\Pay\Events;

class SignFailed extends Event
{
    /**
     * Received data.
     *
     * @var array
     */
    public $data;

    /**
     * Bootstrap.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     */
    public function __construct(string $driver, string $gateway, array $data)
    {
        $this->data = $data;

        parent::__construct($driver, $gateway);
    }
}
