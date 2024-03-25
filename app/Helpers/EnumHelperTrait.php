<?php

namespace App\Helpers;

use ReflectionClass;

trait EnumHelperTrait
{
    public static function enumToKeyValueArray(): array
    {
        $enumReflection = new ReflectionClass(self::class);
        $cases = $enumReflection->getConstants();

        $keyValueArray = [];
        foreach ($cases as $case) {
            $keyValueArray[$case->value] = str($case->name)->headline()->toString();
        }

        return $keyValueArray;
    }

    public static function random(): string
    {
        $enumReflection = new ReflectionClass(self::class);
        $cases = $enumReflection->getConstants();
        $randomKey = array_rand($cases);

        return $cases[$randomKey]->value;
    }

    public static function selectOptions(): array
    {
        $enumReflection = new ReflectionClass(self::class);
        $cases = $enumReflection->getConstants();

        $keyValueArray = [];
        foreach ($cases as $case) {
            $keyValueArray[] = [
                'id' => $case->value,
                'name' => str($case->name)->headline()->toString(),
            ];
        }

        return $keyValueArray;
    }
}
