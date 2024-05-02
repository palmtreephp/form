<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\Constraint\Number as NumberConstraint;
use Palmtree\Form\UploadedFile;

class Size extends AbstractConstraint implements ConstraintInterface
{
    private readonly NumberConstraint $constraint;

    public function __construct(array|string $args = [])
    {
        parent::__construct($args);

        $this->constraint = new NumberConstraint();

        $this->constraint->setMin($args['min_bytes'] ?? 1);

        if (isset($args['max_bytes'])) {
            $this->constraint->setMax($args['max_bytes']);
        }
    }

    public function validate(mixed $input): bool
    {
        return $this->doValidate($input);
    }

    private function doValidate(UploadedFile $input): bool
    {
        if ($this->constraint->validate($input->getSize())) {
            return true;
        }

        $errorCode = $this->constraint->getErrorCode();

        if ($errorCode === NumberConstraint::ERROR_TOO_SMALL) {
            $this->setErrorMessage('File size must be greater than ' . $this->constraint->getMin() . ' bytes');
        } elseif ($errorCode === NumberConstraint::ERROR_TOO_LARGE) {
            $this->setErrorMessage('File size must be less than ' . $this->constraint->getMax() . ' bytes');
        }

        return false;
    }
}
