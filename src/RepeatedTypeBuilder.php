<?php declare(strict_types=1);

namespace Palmtree\Form;

use Palmtree\Form\Constraint\Matching;
use Palmtree\Form\Type\RepeatedType;
use Palmtree\Form\Type\TypeInterface;

class RepeatedTypeBuilder
{
    /** @var FormBuilder */
    private $formBuilder;

    public function __construct(FormBuilder $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    public function build(string $name, array $args): RepeatedType
    {
        $repeatedType = new RepeatedType($args);

        $firstOfType = $this->formBuilder->create($name, $repeatedType->getRepeatableType(), $args);

        $secondArgs   = self::buildSecondArgs($firstOfType, $args);
        $secondOfType = $this->formBuilder->create($secondArgs['name'], $repeatedType->getRepeatableType(), $secondArgs);

        $matchError = $firstOfType->getHumanName() . 's do not match';

        $firstOfType->addConstraint(new Matching([
            'match_field'   => $secondOfType,
            'error_message' => $matchError,
        ]));

        $secondOfType->addConstraint(new Matching([
            'match_field'   => $firstOfType,
            'error_message' => $matchError,
        ]));

        return $repeatedType;
    }

    private static function buildSecondArgs(TypeInterface $firstOfType, array $args): array
    {
        $secondArgs = $args;

        if (!isset($secondArgs['name'])) {
            $secondArgs['name'] = $firstOfType->getName() . '_2';
        }

        if (!isset($secondArgs['label'])) {
            $secondArgs['label'] = 'Confirm ' . $firstOfType->getLabel();
        }

        if (!isset($secondArgs['placeholder'])) {
            $secondArgs['placeholder'] = $firstOfType->getPlaceHolderAttribute() . ' again';
        }

        return $secondArgs;
    }
}
