<?php

namespace App\Transformer\Types;

use App\Models\Document;
use App\Models\Transformer;
use App\Models\DocumentChunk;
use Soundasleep\Html2Text as Helper;
use App\Transformer\BaseTransformer;

class [RESOURCE_CLASS_NAME] extends BaseTransformer
{
    public function handle(Transformer $transformer): Document
    {

        if (str($this->document->guid)->endsWith('.html')) {
            if (! DocumentChunk::query()
            ->where('document_id', $this->document->id)
            ->exists()) {
                $fileContents = Helper::convert($this->document->content, [
                    'ignore_errors' => true,
                ]);

                DocumentChunk::create([
                    'guid' => str($filePath)->afterLast("/"),
                    'content' => $fileContents,
                    'document_id' => $this->document->id,
            ]);
        }
        }

        return $this->document;
    }
}
