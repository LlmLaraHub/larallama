<?php

namespace App\Domains\Sources;

use App\Helpers\EnumHelperTrait;
use App\Models\Collection;

enum SourceTypeEnum: string
{
    use EnumHelperTrait;

    case WebSearchSource = 'web_search_source';
    case EmailSource = 'email_source';
    case EmailBoxSource = 'email_box_source';
    case GenericSource = 'generic_source';
    case WebhookSource = 'webhook_source';
    case JsonSource = 'json_source';
    case FeedSource = 'feed_source';
    //leave for scripting

    public static function ignore(): array
    {
        return [
            self::GenericSource,
        ];
    }

    public static function getAvailableSources(Collection $collection): array
    {
        $enumReflection = new \ReflectionClass(self::class);
        $cases = $enumReflection->getConstants();

        $keyValueArray = [];
        foreach ($cases as $case) {

            if (! in_array($case, self::ignore())) {
                $keyValueArray[] = [
                    'route' => route('collections.sources.'.$case->value.'.create',
                        [
                            'collection' => $collection->id,
                        ]
                    ),
                    'name' => str($case->name)->headline()->toString(),
                    'active' => true,
                ];
            }
        }

        return $keyValueArray;
    }
}
