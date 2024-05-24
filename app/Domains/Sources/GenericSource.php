<?php

namespace App\Domains\Sources;

use App\Models\Source;
use Illuminate\Support\Facades\Log;

class GenericSource extends BaseSource
{
    public function handle(Source $source): void
    {
        $this->source = $source;

        Log::info('[LaraChain] - Running Generic Source Source');

        try {
            if ($source->transformers()->count() === 0) {

                Log::info('No Generic Transformer just yet');
            } else {
                /**
                 * @NOTE
                 * Examples
                 * Example One: Maybe there is 1 transformer to make a reply to the email
                 * Transformer 1 of 1 ReplyTo Email
                 *   Take the email
                 *   Use Collection as voice
                 *   Make reply to email
                 *   The Transformer as an Output attached to it and the reply is sent.
                 *
                 *  Example Two: CRM Transformer
                 *    Take the email and make document (Type Email) and chunks from the email
                 *    After that take the content and make who is it to, who is it from
                 *    and make Documents for each for those of type Contact
                 *    Relate those to the document (Type Email)
                 *    and now there are relations for later use
                 */
                Log::info("[LaraChain] - Source has Transformers let's figure out which one to run");
            }

        } catch (\Exception $e) {
            Log::error('[LaraChain] - Error running Generic Source', [
                'error' => $e->getMessage(),
            ]);
        }

    }
}
