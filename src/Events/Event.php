<?php

namespace Jinowom\Pay\Events;

use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

class Event extends SymfonyEvent
{
    /**
     * Driver.
     *
     * @var string
     */
    public $driver;

    /**
     * Method.
     *
     * @var string
     */
    public $gateway;

    /**
     * Extra attributes.
     *
     * @var mixed
     */
    public $attributes;

    /**
     * Bootstrap.
     *
     * @author cocoli6000 <cocoli6000@gmail.com>
     */
    public function __construct(string $driver, string $gateway)
    {
        $this->driver = $driver;
        $this->gateway = $gateway;
    }
}
