# LLmDriver

## Todo move this to the Laravel Module library

[https://github.com/nWidart/laravel-modules](https://github.com/nWidart/laravel-modules)


For now just taking notes of things I need to remember.


Load Provider.

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
    \LlmLaraHub\LlmDriver\LlmServiceProvider::class,
];
```