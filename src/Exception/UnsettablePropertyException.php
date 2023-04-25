<?php

declare(strict_types=1);

namespace Palmtree\Form\Exception;

class UnsettablePropertyException extends \LogicException
{
    /**
     * @param object $object
     */
    public function __construct(string $property, $object)
    {
        $class = \get_class($object);

        parent::__construct("Property '$property' is not settable on object of type '$class'");
    }
}
