<?php declare(strict_types=1);

namespace Palmtree\Form;

use Palmtree\Form\Exception\InvalidTypeException;
use Palmtree\Form\Type\TypeInterface;

class TypeLocator
{
    /** @var array<string, string> Map of types where key is the shorthand name e.g 'text' and value is the FCQN. */
    private static $types = [];

    private const TYPE_KEYS = [
        'entry_type',
        'repeated_type',
    ];

    public function __construct()
    {
        if (empty(self::$types)) {
            foreach (glob(__DIR__ . '/Type/*Type.php', GLOB_NOSORT) ?: [] as $file) {
                self::$types[strtolower(basename($file, 'Type.php'))] = __NAMESPACE__ . '\\Type\\' . basename($file, '.php');
            }
        }
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
                throw new InvalidTypeException(sprintf("Type must be an instance of '%s'. '%s' given", TypeInterface::class, \get_class($type)));
            }

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
