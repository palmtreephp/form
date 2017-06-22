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

                if (!is_null($min)) {
                    $element->addAttribute('min', $min);
                }

                if (!is_null($max)) {
                    $element->addAttribute('max', $max);
                }
            }
        }

        return $element;
    }
}
