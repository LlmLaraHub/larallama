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

    public static function values(): array
    {
        return collect(self::cases())->map(
            function ($item) {
                return $item->value;
            }
        )->toArray();
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

    public static function toArray(): array
    {
        $items = [];

        foreach (self::cases() as $item) {
            $items[$item->value] = $item->name;
        }

        return $items;
    }

    public static function valuesTitleCase(): array
    {
        return collect(self::cases())->map(
            function ($item) {
                return str($item->value)->headline()->toString();
            }
        )->toArray();
    }
}
