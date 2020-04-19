<?php

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint;
use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;

class Size extends AbstractConstraint implements ConstraintInterface
{
    /** @var Constraint\Number */
    private $constraint;

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        $this->constraint = new Constraint\Number();

        $this->constraint->setMin($args['min_bytes'] ?? 1);

        if (isset($args['max_bytes'])) {
            $this->constraint->setMax($args['max_bytes']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate($uploadedFile)
    {
        $size = (int)$uploadedFile['size'];

        if ($this->constraint->validate($size)) {
            return true;
        }

        $errorCode = $this->constraint->getErrorCode();

        if ($errorCode === Constraint\Number::ERROR_TOO_SMALL) {
            $this->setErrorMessage('File size must be greater than ' . $this->constraint->getMin() . ' bytes');
        } elseif ($errorCode === Constraint\Number::ERROR_TOO_LARGE) {
            $this->setErrorMessage('File size must be less than ' . $this->constraint->getMax() . ' bytes');
        }

        return false;
    }
}
