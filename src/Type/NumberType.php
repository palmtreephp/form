<?php

namespace Palmtree\Form\Type;

use Palmtree\Form\Constraint\Number;

class NumberType extends AbstractType
{
    protected $type = 'number';

    public function getElement()
    {
        $element = parent::getElement();

        foreach ($this->getConstraints() as $constraint) {
            if ($constraint instanceof Number) {
                $min = $constraint->getMin();
                $max = $constraint->getMax();

                if (null !== $min) {
                    $element->addAttribute('min', $min);
                }

                if (null !== $max) {
                    $element->addAttribute('max', $max);
                }
            }
        }

        return $element;
    }
}
