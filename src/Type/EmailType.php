<?php declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Form\Constraint\Email;

class EmailType extends TextType
{
    protected $type = 'email';

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        if ($this->required) {
            $this->addConstraint(new Email());
        }
    }
}
