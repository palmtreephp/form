<?php

namespace Palmtree\Form\Type;

class PersonType extends AbstractGroupType
{
    public function build()
    {
        $this
            ->add('name', TextType::class, [
            ])
            ->add('email', EmailType::class, [
            ]);
    }
}
