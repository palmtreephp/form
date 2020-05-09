<?php

namespace Palmtree\Form;

use Palmtree\Form\Exception\InvalidTypeException;
use Palmtree\Form\Type\TypeInterface;

class TypeLocator
{
    /** @var array */
    private static $types;

    public function __construct()
    {
        $this->findTypeClasses();
    }

    public function getTypeClass(string $type): ?string
    {
        if (isset(self::$types[$type])) {
            return self::$types[$type];
        }

        if (class_exists($type)) {
            return $type;
        }

        return null;
    }

    /**
     * Returns a new instance of the given form type.
     *
     * @param string|object $type Fully-qualified class name of the form type or short hand e.g 'text', 'email'.
     *                            Can also be a pre-constructed instance.
     * @param array         $args Arguments to pass to the type class constructor.
     */
    public function getTypeObject($type, array $args): TypeInterface
    {
        if (\is_object($type)) {
            if (!$type instanceof TypeInterface) {
                throw new InvalidTypeException('Type must be an instance of ' . TypeInterface::class . '. ' . \get_class($type) . ' given');
            }

            return $type;
        }

        $class = $this->getTypeClass($type);

        if (!is_subclass_of($class, TypeInterface::class, true)) {
            throw new InvalidTypeException('Type must be an instance of' . TypeInterface::class . ". '$type' given");
        }

        return new $class($args, $this);
    }

    private function findTypeClasses(): void
    {
        if (self::$types === null) {
            self::$types = [];
            foreach (glob(__DIR__ . '/Type/*Type.php', GLOB_NOSORT) ?: [] as $file) {
                self::$types[strtolower(basename($file, 'Type.php'))] = __NAMESPACE__ . '\\Type\\' . basename($file, '.php');
            }
        }
    }
}
