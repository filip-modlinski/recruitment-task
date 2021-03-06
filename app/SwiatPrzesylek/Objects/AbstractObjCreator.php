<?php


namespace App\SwiatPrzesylek\Objects;


abstract class AbstractObjCreator implements ObjCreatorInterface
{
    /**
     * @return array
     */
    public function getArray(): array
    {
        return array_filter(get_object_vars($this), function ($value) { return !empty($value); });
    }
}
