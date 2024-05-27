<?php

namespace App\Domains\Prompts;

class PromptMerge
{
    public static function merge(array $tokens, array $content, string $prompt): string
    {

        foreach ($tokens as $index => $token) {
            $tokenContent = data_get($content, $index);
            if ($tokenContent) {
                $prompt = str($prompt)
                    ->replace("$token", $tokenContent)
                    ->toString();
            }
        }

        return $prompt;
    }
}
