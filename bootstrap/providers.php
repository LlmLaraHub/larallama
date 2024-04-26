<?php

use LlmLaraHub\WebSearch\Providers\WebSearchServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
    \LlmLaraHub\LlmDriver\LlmServiceProvider::class,
];
