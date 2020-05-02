<?php

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\Constraint\Number as NumberConstraint;
use Palmtree\Form\UploadedFile;

class Size extends AbstractConstraint implements ConstraintInterface
{
    /** @var NumberConstraint */
    private $constraint;

    public function __construct($args = [])
    {
        parent::__construct($args);

        $this->constraint = new NumberConstraint();

        $this->constraint->setMin($args['min_bytes'] ?? 1);

        if (isset($args['max_bytes'])) {
            $this->constraint->setMax($args['max_bytes']);
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     */
    public function validate($uploadedFile): bool
    {
        if ($this->constraint->validate($uploadedFile->getSize())) {
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
