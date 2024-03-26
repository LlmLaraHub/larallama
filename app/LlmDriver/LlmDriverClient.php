<?php 

namespace App\LlmDriver;

use Mockery\Mock;

class LlmDriverClient 
{
    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public static function make(): BaseClient
    {
        $driver = config('llmdriver.driver');

        $config = config("llmdriver.drivers.{$driver}");

        if(!method_exists(static::class, $driver)) {
            throw new \Exception("Driver {$driver} not found");
        }

        return (new static($config))->$driver();
    }

    public function mock(): BaseClient
    {
        return new MockClient();
    }
    
}