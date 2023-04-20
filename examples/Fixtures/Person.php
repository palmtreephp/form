<?php

namespace Palmtree\Form\Examples\Fixtures;

use Palmtree\Form\DataMapper\DataMapperInterface;
use Palmtree\Form\DataMapper\ObjectDataMapper;

class Person implements DataMapperInterface
{
    use ObjectDataMapper;

    /** @var string */
    public $name;
    /** @var string */
    public $emailAddress;
    /** @var int */
    private $age;

    public function setAge($age): void
    {
        $this->age = (int)$age;
    }

    public function getAge(): int
    {
        return $this->age;
    }
}
