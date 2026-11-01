<?php

declare(strict_types=1);

namespace Palmtree\Form;

use Palmtree\NameConverter\SnakeCaseToCamelCaseNameConverter;

/**
 * Applies snake_case options to an object through its setters, falling back to an adder for plural keys,
 * e.g. 'error_message' calls setErrorMessage() and 'constraints' calls addConstraint().
 *
 * @internal
 */
final class OptionSetter
{
    /**
     * Calls the setter or adder for each key in $args and returns the args that had neither.
     *
     * @param array<array-key, mixed> $args
     *
     * @return array<array-key, mixed>
     */
    public static function apply(object $target, array $args): array
    {
        foreach ($args as $key => $value) {
            $method = self::methodFor($target, (string)$key);

            if ($method === null) {
                continue;
            }

            if (\is_array($value) && ((new \ReflectionMethod($target, $method))->getParameters()[0] ?? null)?->isVariadic()) {
                $target->$method(...$value);
            } else {
                $target->$method($value);
            }

            unset($args[$key]);
        }

        return $args;
    }

    public static function methodFor(object $target, string $key): ?string
    {
        $property = ucfirst((new SnakeCaseToCamelCaseNameConverter())->normalize($key));

        if (\is_callable([$target, "set$property"])) {
            return "set$property";
        }

        if (str_ends_with($property, 's') && \is_callable([$target, 'add' . substr($property, 0, -1)])) {
            return 'add' . substr($property, 0, -1);
        }

        return null;
    }
}
