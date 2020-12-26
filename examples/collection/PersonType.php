<?php declare(strict_types=1);

namespace Palmtree\Form\Type;

class PersonType extends AbstractGroupType
{
    public function build(): void
    {
        $this
            ->add('name', TextType::class, [
            ])
            ->add('email', EmailType::class, [
            ]);
    }
}
