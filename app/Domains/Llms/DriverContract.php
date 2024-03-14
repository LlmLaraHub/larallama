<?php

namespace App\Domains\Llms;

abstract class DriverContract
{
    protected $client;

    abstract public static function make(): self;
}
