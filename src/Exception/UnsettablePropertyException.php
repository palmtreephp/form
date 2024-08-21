<?php

declare(strict_types=1);

namespace Palmtree\Form\Exception;

class UnsettablePropertyException extends OutOfBoundsException
{
    public function __construct(string $property, object $object)
    {
        parent::__construct(\sprintf("Property %s is not settable on object of type '%s'", $property, $object::class));
    }
}
