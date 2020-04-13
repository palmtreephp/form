<?php

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint;
use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;

class Size extends AbstractConstraint implements ConstraintInterface
{
    private $constraint;

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        $this->constraint = new Constraint\Number();

        $minBytes = isset($args['min_bytes']) ? $args['min_bytes'] : 1;

        $this->constraint->setMin($minBytes);

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

        $errorNo = $this->constraint->getErrorCode();

        if ($errorNo === Constraint\Number::ERROR_TOO_SMALL) {
            $this->setErrorMessage(sprintf('File size must be greater than %d bytes', $this->constraint->getMin()));
        } elseif ($errorNo === Constraint\Number::ERROR_TOO_LARGE) {
            $this->setErrorMessage(sprintf('File size must be less than %d bytes', $this->constraint->getMax()));
        }

        return false;
    }
}
