<?php

namespace Palmtree\Form\Test\Fixtures;

use Palmtree\Form\Type\AbstractGroupType;

class PersonType extends AbstractGroupType
{
    public function build(): void
    {
        $this
            ->add('name', 'text')
            ->add('emailAddress', 'text')
            ->add('age', 'number')
            ->add('signup', 'checkbox', [
                'required' => false,
            ])
            ->add('favouriteConsole', 'choice', [
                'placeholder' => 'Select a console',
                'choices'     => [
                    'PlayStation' => 'PlayStation',
                    'Xbox'        => 'Xbox',
                    'Switch'      => 'Switch',
                ],
            ])
            ->add('interests', 'choice', [
                'multiple' => true,
                'expanded' => true,
                'choices'  => [
                    'football' => 'Football',
                    'gaming'   => 'Gaming',
                    'music'    => 'Music',
                ],
            ])
            ->add('pets', 'collection', [
                'entry_type' => 'text',
            ])
        ;
    }
}
