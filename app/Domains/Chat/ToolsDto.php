<?php

namespace App\Domains\Chat;

use LlmLaraHub\LlmDriver\Functions\FunctionCallDto;
use Spatie\LaravelData\Data;

class ToolsDto extends Data
{
    /**
     * @param  FunctionCallDto[]  $tools
     */
    public function __construct(
        public array $tools = []
    ) {
    }
}
