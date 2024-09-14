<?php

namespace App\Domains\Outputs;

use App\Helpers\EnumHelperTrait;
use App\Models\Collection;

enum OutputTypeEnum: string
{
    use EnumHelperTrait;

    case WebPage = 'web_page';
    case EmailOutput = 'email_output';
    case CalendarOutput = 'calendar_output';
    case ApiOutput = 'api_output';
    case EmailReplyOutput = 'email_reply_output';
    //leave for scripting

    public static function ignore(): array
    {
        return [
        ];
    }

    public static function getAvailable(Collection $collection): array
    {
        $enumReflection = new \ReflectionClass(self::class);
        $cases = $enumReflection->getConstants();

        $keyValueArray = [];
        foreach ($cases as $case) {

            if (! in_array($case, self::ignore())) {
                $keyValueArray[] = [
                    'route' => route('collections.outputs.'.$case->value.'.create',
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
