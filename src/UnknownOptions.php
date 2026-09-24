<?php

declare(strict_types=1);

namespace Palmtree\Form;

use Palmtree\NameConverter\SnakeCaseToCamelCaseNameConverter;

/**
 * Reports options that nothing will read, which are usually typos such as 'requried'.
 *
 * @internal
 */
final class UnknownOptions
{
    /**
     * Triggers a deprecation for each key in $args that is neither in $knownKeys nor handled by a setter or adder
     * on $target, following the same naming rules as ArgParser::parseSetters().
     *
     * @param array<array-key, mixed> $args
     * @param list<string>            $knownKeys
     */
    public static function deprecate(object $target, array $args, array $knownKeys = []): void
    {
        $nameConverter = new SnakeCaseToCamelCaseNameConverter();

        foreach (array_keys($args) as $key) {
            $key = (string)$key;

            if (\in_array($key, $knownKeys, true) || self::hasSetter($target, $nameConverter->normalize($key))) {
                continue;
            }

            @trigger_error(\sprintf(
                'Passing the unknown option "%s" to %s is deprecated. It is ignored, and will throw an exception in the next major version.',
                $key,
                $target::class,
            ), \E_USER_DEPRECATED);
        }
    }

    private static function hasSetter(object $target, string $property): bool
    {
        if (\is_callable([$target, 'set' . ucfirst($property)])) {
            return true;
        }

        return str_ends_with($property, 's') && \is_callable([$target, 'add' . ucfirst(substr($property, 0, -1))]);
    }
}
