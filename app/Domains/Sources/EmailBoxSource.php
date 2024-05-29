<?php

namespace App\Domains\Sources;

use App\Models\Source;
use Facades\App\Domains\EmailParser\EmailClient;
use Facades\App\Domains\Transformers\EmailTransformer;
use Illuminate\Support\Facades\Log;

class EmailBoxSource extends EmailSource
{
    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::EmailBoxSource;

    public static string $description = 'Email Box Source gets and removes email from an email account of your own.
    Just add the needed connection information and you are good to go.';

    public function handle(Source $source): void
    {
        $mails = EmailClient::handle($source);
        $this->source = $source;

        foreach ($mails as $mailDto) {
            $this->mailDto = $mailDto;

            $this->content = $this->mailDto->getContent();

            $this->documentSubject = $this->mailDto->subject;

            $this->meta_data = $this->mailDto->toArray();

            $this->transformers = $source->transformers;

            Log::info('[LaraChain] - Running Email Source');

            try {
                Log::info('Do something!');
                $baseSource = EmailTransformer::transform(baseSource: $this);
                foreach ($source->transformers as $transformerChainLink) {
                    Log::info("[LaraChain] - Source has Transformers let's figure out which one to run", [
                        'type' => $transformerChainLink->type->name,
                    ]);

                    $class = '\\App\\Domains\\Transformers\\'.$transformerChainLink->type->name;
                    if (class_exists($class)) {
                        $facade = '\\Facades\\App\\Domains\\Transformers\\'.$transformerChainLink->type->name;
                        $baseSource = $facade::transform($this);
                    } else {
                        Log::info('[LaraChain] - No Class found ', [
                            'class' => $class,
                        ]);
                    }
                }

                $this->batchTransformedSource($baseSource, $source);
            } catch (\Exception $e) {
                Log::error('[LaraChain] - Error running Email Source', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

    }
}
