<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Form\Constraint\Number;
use Palmtree\Html\Element;

trait NumberTypeTrait
{
    public function getElement(): Element
    {
        $element = parent::getElement();

        foreach ($this->constraints as $constraint) {
            if ($constraint instanceof Number) {
                $min = $constraint->getMin();
                $max = $constraint->getMax();

                if ($min !== null) {
                    $element->attributes['min'] = (string)$min;
                }

                if ($max !== null) {
                    $element->attributes['max'] = (string)$max;
                }

                break;
            }
        }

        return $element;
    }
}
