<?php declare(strict_types=1);

namespace Palmtree\Form\Examples\Fixtures;

use Palmtree\Form\Type\AbstractGroupType;
use Palmtree\Form\Type\EmailType;
use Palmtree\Form\Type\NumberType;
use Palmtree\Form\Type\TextType;

class PersonType extends AbstractGroupType
{
    public function build(): void
    {
        $this
            ->add('name', TextType::class, [
            ])
            ->add('email', EmailType::class, [
            ])
            ->add('age', NumberType::class)
        ;
    }
}
