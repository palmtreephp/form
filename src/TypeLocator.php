<?php

declare(strict_types=1);

namespace Palmtree\Form;

use Palmtree\Form\Exception\InvalidTypeException;
use Palmtree\Form\Type\TypeInterface;

/**
 * @phpstan-type TypeType = TypeInterface|class-string<TypeInterface>|string
 */
class TypeLocator
{
    /** @var array<string, class-string<TypeInterface>> Map of types where key is the shorthand name e.g 'text' and value is the fully-qualified class name. */
    private static array $types = [];

    private const TYPE_KEYS = [
        'entry_type',
        'repeated_type',
    ];

    public function __construct()
    {
        if (empty(self::$types)) {
            foreach (glob(__DIR__ . '/Type/*Type.php', \GLOB_NOSORT) ?: [] as $file) {
                /** @var class-string<TypeInterface> $fcqn */
                $fcqn = __NAMESPACE__ . '\\Type\\' . basename($file, '.php');
                self::$types[strtolower(basename($file, 'Type.php'))] = $fcqn;
            }
        }
    }

    /**
     * @param class-string<TypeInterface>|string $type
     *
     * @return class-string<TypeInterface>|null
     */
    public function getTypeClass(string $type): ?string
    {
        if (isset(self::$types[$type])) {
            return self::$types[$type];
        }

        if (class_exists($type) && is_a($type, TypeInterface::class, true)) {
            return $type;
        }

        return null;
    }

    /**
     * Returns a new instance of the given form type.
     *
     * @param TypeType             $type Fully-qualified class name of the form type or shorthand e.g 'text', 'email'.
     *                                   Can also be a pre-constructed instance.
     * @param array<string, mixed> $args Arguments to pass to the type class constructor.
     */
    public function getTypeObject(TypeInterface|string $type, array $args): TypeInterface
    {
        if ($type instanceof TypeInterface) {
            return $type;
        }

        $class = $this->getTypeClass($type);

        if ($class === null) {
            throw new InvalidTypeException('Type could not be found');
        } elseif (!is_subclass_of($class, TypeInterface::class, true)) {
            throw new InvalidTypeException(sprintf("Type must be an instance of '%s'. '%s' given", TypeInterface::class, $type));
        }

        foreach ($args as $key => $value) {
            if (\in_array($key, self::TYPE_KEYS)) {
                $args[$key] = $this->getTypeClass($value);
            }
        }

        return new $class($args, $this);
    }
}
