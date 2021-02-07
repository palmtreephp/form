<?php declare(strict_types=1);

namespace Palmtree\Form\Constraint;

use Palmtree\Form\Type\TypeInterface;

class Match extends AbstractConstraint implements ConstraintInterface
{
    /** @var TypeInterface */
    private $matchField;
    /** @var string */
    protected $errorMessage = 'Fields do not match';

    public function validate($input): bool
    {
        return $this->doValidate($input);
    }

    private function doValidate(string $input): bool
    {
        return $input === $this->matchField->getData();
    }

    public function getMatchField(): TypeInterface
    {
        return $this->matchField;
    }

    public function setMatchField(TypeInterface $matchField): self
    {
        $this->matchField = $matchField;

        return $this;
    }
}
