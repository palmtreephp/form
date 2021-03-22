<?php declare(strict_types=1);

namespace Palmtree\Form\Constraint;

class Email extends AbstractConstraint implements ConstraintInterface
{
    /** @var string */
    protected $errorMessage = 'Please enter a valid email address';

    public function validate($input): bool
    {
        return $this->doValidate($input);
    }

    private function doValidate(string $input): bool
    {
        return filter_var($input, \FILTER_VALIDATE_EMAIL) !== false;
    }
}
