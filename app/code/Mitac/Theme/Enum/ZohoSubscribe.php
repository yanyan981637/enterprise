<?php

namespace Mitac\Theme\Enum;

enum ZohoSubscribe: int
{
    case Subscribe = 1;
    case Unsubscribe = 2;


    public static function getTypeOptions(): array
    {
         $options= [];
         foreach (self::cases() as $item) {
             $options[] = [
                 'value' => $item->value,
                 'label' => __($item->name),
             ];
         }
        return $options;
    }

}
