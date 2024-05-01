<?php

namespace App\Domains\Sources\WebSearch;

use RoachPHP\Support\Configurable;
use RoachPHP\Support\ConfigurableInterface;

class ConfigurationLoader implements ConfigurableInterface
{
    use Configurable;

    public array $urls;
}
