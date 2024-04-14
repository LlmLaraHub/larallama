<?php

namespace LlmLaraHub\TagFunction\Functions;

use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Functions\FunctionCallDto;
use LlmLaraHub\LlmDriver\Functions\FunctionContract;
use LlmLaraHub\LlmDriver\Functions\PropertyDto;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use LlmLaraHub\TagFunction\Contracts\TaggableContract;

class TaggingFunction extends FunctionContract
{
    protected string $name = 'tagging_function';

    protected string $description = 'Used to tag a user input with a tag or tags.';

    public function handle(
        array $messageArray,
        HasDrivers|TaggableContract $model,
        FunctionCallDto $functionCallDto): FunctionResponse
    {
        Log::info('[LaraChain] Tagging function called');

        $summary = $model->getSummary();

        $tags = data_get($functionCallDto->arguments, 'tags', 'no limit');

        $prompt = <<<EOD
This content needs tagging. Please return a list of tags that would apply to this content as JSON array like: ["tag1", "tag2", "tag3"]
They might want you to limit it to a list of tags like: $tags 
But if it says 'no limits' then just return the tags that apply.
Consider year as a tag as well if that is seen in the content.

### START CONTENT
$summary
### END CONTENT
EOD;
        $messagesArray = []; //just to reset it
        $messagesArray[] = MessageInDto::from([
            'content' => $prompt,
            'role' => 'user',
        ]);

        $results = LlmDriverFacade::driver($model->getDriver())->chat($messagesArray);

        $tags = json_decode($results->content, true);

        foreach ($tags as $tag) {
            $model->addTag($tag);
        }

        return FunctionResponse::from(
            [
                'content' => implode(', ', $tags),
            ]
        );
    }

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [
            new PropertyDto(
                name: 'limit_tags',
                description: 'This is a comma separated list of tags to limit the results by if needed',
                type: 'string',
                required: false,
            ),
        ];
    }
}
