<?php

namespace App\Console\Commands\Generators;

use Facades\App\Domains\Generators\Source\GeneratorRepository;
use Illuminate\Console\Command;

class SourceGenerator extends Command
{
    public $signature = 'laralamma:source:create';

    public $description = 'Generate the needed files for a new Source';

    public function handle(): int
    {
        $name = $this->ask('What is the name of this Source type, example WebFile');
        $description = $this->ask('A description to let the user know what it is for');

        $results = sprintf("Does this look right, the name is %s, the description is %s'.",
            $name,
            $description);

        if (! $this->confirm($results)) {
            $this->error('Ok try again');
            exit();
        }

        if (! str($name)->endsWith('Source')) {
            $this->error('The name must end with Source eg FooBarSource');
            exit();
        }

        if (! $this->confirm('Lastly make sure your git status is clean so you can see what this command will output')) {
            $this->error('Ok come back in a moment after you git add .');
            exit();
        }

        $this->comment('Ok gonna go make the Generator one moment.');

        GeneratorRepository::setup($name, $description, false)->run();

        $this->comment('All done, check your git status and update the app/Domains/Sources/SourceTypeEnum.php to have '.$name, ' and value '.str($name)->snake()->toString());

        return self::SUCCESS;
    }
}
