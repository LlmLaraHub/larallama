<?php

namespace App\Domains\Generators\Source;

use App\Domains\Generators\Base;
use App\Domains\Generators\BaseRepository;
use Facades\App\Domains\Generators\TokenReplacer;
use Illuminate\Support\Facades\File;

class RoutesSource extends Base
{
    public function handle(BaseRepository $generatorRepository): void
    {
        $this->generatorRepository = $generatorRepository;
        $routes = <<<'EOD'

    Route::controller(\App\Http\Controllers\Sources\[RESOURCE_CLASS_NAME]Controller::class)->group(
        function () {
            Route::get('/collections/{collection}/sources/[RESOURCE_KEY]/create', 'create')
                ->name('collections.sources.[RESOURCE_KEY].create');
            Route::post('/collections/{collection}/sources/[RESOURCE_KEY]', 'store')
                ->name('collections.sources.[RESOURCE_KEY].store');
            Route::get('/collections/{collection}/sources/[RESOURCE_KEY]/{source}/edit', 'edit')
                ->name('collections.sources.[RESOURCE_KEY].edit');
            Route::put('/collections/{collection}/sources/[RESOURCE_KEY]/{source}/update', 'update')
                ->name('collections.sources.[RESOURCE_KEY].update');
        }
    );

EOD;
        $routesTransformed = TokenReplacer::handle($generatorRepository, $routes);
        $routesPath = base_path('routes/web.php');

        $routesOriginal = File::get($routesPath);

        $routesOriginalUpdated = str($routesOriginal)
            ->append($routesTransformed)->toString();

        File::put($routesPath, $routesOriginalUpdated);
    }
}
