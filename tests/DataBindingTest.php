<?php

namespace Palmtree\Form\Test;

use Palmtree\Form\Form;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Test\Fixtures\Person;
use PHPUnit\Framework\TestCase;

class DataBindingTest extends TestCase
{
    public function testDataBinding()
    {
        $person = new Person();

        $form = $this->buildForm($person);

        $form->submit([
            'name'             => 'John Smith',
            'emailAddress'     => 'john.smith@example.org',
            'age'              => 42,
            'favouriteConsole' => 'PlayStation',
            'interests'        => ['football', 'gaming'],
        ]);

        self::assertSame($person->name, 'John Smith');
        self::assertSame($person->emailAddress, 'john.smith@example.org');
        self::assertSame($person->getAge(), 42);
        self::assertSame($person->getFavouriteConsole(), 'PlayStation');
        self::assertSame($person->interests, ['football', 'gaming']);
    }

    private function buildForm(Person $person): Form
    {
        $builder = new FormBuilder([], $person);

        $builder
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

        return $builder->getForm();
    }
}
