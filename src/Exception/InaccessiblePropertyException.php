<?php

declare(strict_types=1);

namespace Palmtree\Form\Exception;

class InaccessiblePropertyException extends OutOfBoundsException
{
    public function __construct(string $property, object $object)
    {
        parent::__construct(sprintf("Property %s is not accessible on object of type '%s'", $property, $object::class));
    }
}
