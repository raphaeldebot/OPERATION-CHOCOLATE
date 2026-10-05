<?php

declare(strict_types=1);

namespace model;

abstract class AbstractMapping
{
    public function __construct(array $datas = [])
    {
        $this->hydrate($datas);
    }

    protected function hydrate(array $datas): void
    {
        foreach ($datas as $property => $value) {

            $setterName = 'set' . str_replace(
                '_',
                '',
                ucwords($property, '_')
            );

            if (method_exists($this, $setterName)) {
                $this->$setterName($value);
            }
        }
    }
}