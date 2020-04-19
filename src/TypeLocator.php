<?php

namespace Palmtree\Form;

use Palmtree\Form\Type\AbstractType;
use Palmtree\Form\Type\TextType;

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
     * @param string $type FQCN of the form type or short hand e.g 'text', 'email'.
     * @param array  $args Arguments to pass to the type class constructor.
     */
    public function getTypeObject(string $type, array $args): AbstractType
    {
        if ($type instanceof AbstractType) {
            return $type;
        }

        $class = $this->getTypeClass($type);

        if (!class_exists($class)) {
            $class = TextType::class;
        }

        return new $class($args, $this);
    }

    private function findTypeClasses(): void
    {
        if (self::$types === null) {
            self::$types = [];
            foreach (glob(__DIR__ . '/Type/*Type.php') ?: [] as $file) {
                $type = basename($file, 'Type.php');

                self::$types[strtolower($type)] = __NAMESPACE__ . '\\Type\\' . basename($file, '.php');
            }
        }
    }
}
