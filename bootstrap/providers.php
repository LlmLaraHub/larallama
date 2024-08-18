<?php

return [
    App\Domains\Sources\WebSearch\WebSearchProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
    LlmLaraHub\LlmDriver\LlmServiceProvider::class,
];
