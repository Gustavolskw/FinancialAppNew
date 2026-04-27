<?php

namespace App\Infrastructure\Helper;

use Doctrine\Common\Collections\Collection;

class BaseHelper
{

    public static function getParamValueByName(Collection $collection, string $name): mixed
    {
        foreach ($collection as $param) {
            if ($param->getName() === $name) {
                return $param->getValue();
            }
        }
        return null;
    }
}
