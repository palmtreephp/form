<?php

namespace Palmtree\Form\Type;

use Palmtree\Form\Constraint\Email;

class EmailType extends TextType
{
    protected $type = 'email';

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        $this->addConstraint(new Email());
    }
}
