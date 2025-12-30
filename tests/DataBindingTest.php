<?php

declare(strict_types=1);

namespace Palmtree\Form\Test;

use Palmtree\Form\Exception\OutOfBoundsException;
use Palmtree\Form\Form;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Test\Fixtures\Person;
use Palmtree\Form\Test\Fixtures\PersonType;
use Palmtree\Form\Type\CollectionType;
use PHPUnit\Framework\TestCase;

class DataBindingTest extends TestCase
{
    public function testObjectTwoWayDataBinding(): void
    {
        $person = new Person();

        $form = $this->buildForm($person);

        $form->submit([
            'name' => 'John Smith',
            'emailAddress' => 'john.smith@example.org',
            'age' => 42,
            'favouriteConsole' => 'PlayStation',
            'interests' => ['football', 'gaming'],
        ]);

        $this->assertSame('John Smith', $person->name);
        $this->assertSame('john.smith@example.org', $person->emailAddress);
        $this->assertSame(42, $person->getAge());
        $this->assertSame('PlayStation', $person->getFavouriteConsole());
        $this->assertSame(['football', 'gaming'], $person->interests);
    }

    public function testArrayOneWayDataBinding(): void
    {
        $person = [
            'name' => 'John Smith',
            'emailAddress' => 'john.smith@example.org',
            'age' => 42,
            'favouriteConsole' => 'PlayStation',
            'interests' => ['football', 'gaming'],
            'signup' => false,
            'pets' => [],
        ];

        $form = $this->buildForm($person);

        $this->assertSame('John Smith', $form->get('name')->getData());
        $this->assertSame('john.smith@example.org', $form->get('emailAddress')->getData());
        $this->assertSame(42, $form->get('age')->getData());
        $this->assertSame('PlayStation', $form->get('favouriteConsole')->getData());
        $this->assertSame(['football', 'gaming'], $form->get('interests')->getData());
    }

    public function testArrayAccessTwoWayDataBinding(): void
    {
        $person = new \ArrayObject([
            'name' => 'John Smith',
            'emailAddress' => 'john.smith@example.org',
            'age' => 42,
            'favouriteConsole' => 'PlayStation',
            'interests' => ['football', 'gaming'],
            'signup' => false,
            'pets' => [],
        ]);

        $form = $this->buildForm($person);

        $this->assertSame('John Smith', $form->get('name')->getData());
        $this->assertSame('john.smith@example.org', $form->get('emailAddress')->getData());
        $this->assertSame(42, $form->get('age')->getData());
        $this->assertSame('PlayStation', $form->get('favouriteConsole')->getData());
        $this->assertSame(['football', 'gaming'], $form->get('interests')->getData());

        $form->submit([
            'name' => 'Bob Smith',
            'emailAddress' => 'bob.smith@example.org',
            'age' => 45,
            'favouriteConsole' => 'Xbox',
            'interests' => ['guitar'],
        ]);

        $this->assertSame('Bob Smith', $person['name']);
        $this->assertSame('bob.smith@example.org', $person['emailAddress']);
        $this->assertSame(45, $person['age']);
        $this->assertSame('Xbox', $person['favouriteConsole']);
        $this->assertSame(['guitar'], $person['interests']);
    }

    public function testStdClassTwoWayDataBinding(): void
    {
        $person = new \stdClass();
        $person->name = 'John Smith';
        $person->emailAddress = 'john.smith@example.org';
        $person->age = 42;
        $person->favouriteConsole = 'PlayStation';
        $person->interests = ['football', 'gaming'];
        $person->signup = false;
        $person->pets = [];

        $form = $this->buildForm($person);

        $this->assertSame('John Smith', $form->get('name')->getData());
        $this->assertSame('john.smith@example.org', $form->get('emailAddress')->getData());
        $this->assertSame(42, $form->get('age')->getData());
        $this->assertSame('PlayStation', $form->get('favouriteConsole')->getData());
        $this->assertSame(['football', 'gaming'], $form->get('interests')->getData());

        $form->submit([
            'name' => 'Bob Smith',
            'emailAddress' => 'bob.smith@example.org',
            'age' => 45,
            'favouriteConsole' => 'Xbox',
            'interests' => ['guitar'],
        ]);

        $this->assertSame('Bob Smith', $person->name);
        $this->assertSame('bob.smith@example.org', $person->emailAddress);
        $this->assertSame(45, $person->age);
        $this->assertSame('Xbox', $person->favouriteConsole);
        $this->assertSame(['guitar'], $person->interests);
    }

    public function testArrayDataMapperThrowsOutOfBoundsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Key 'pets' not found in bound data with the following keys: name, emailAddress, age, favouriteConsole, interests, signup");

        $person = [
            'name' => 'John Smith',
            'emailAddress' => 'john.smith@example.org',
            'age' => 42,
            'favouriteConsole' => 'PlayStation',
            'interests' => ['football', 'gaming'],
            'signup' => false,
        ];

        $this->buildForm($person);
    }

    public function testArrayAccessDataMapperThrowsOutOfBoundsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Key 'signup' not found in bound data with the following keys: name, emailAddress, age, favouriteConsole, interests, pets");

        $person = new \ArrayObject([
            'name' => 'John Smith',
            'emailAddress' => 'john.smith@example.org',
            'age' => 42,
            'favouriteConsole' => 'PlayStation',
            'interests' => ['football', 'gaming'],
            'signup' => false,
            'pets' => [],
        ]);

        $form = $this->buildForm($person);

        unset($person['signup']);

        $form->submit([
            'name' => 'Bob Smith',
            'emailAddress' => 'bob.smith@example.org',
            'age' => 45,
            'favouriteConsole' => 'Xbox',
            'interests' => ['guitar'],
        ]);
    }

    public function testCollectionOneWayDataBinding(): void
    {
        $data = [
            'people' => [
                [
                    'name' => 'John Smith',
                    'emailAddress' => 'john.smith@example.org',
                    'age' => 42,
                    'favouriteConsole' => 'PlayStation',
                    'interests' => ['football', 'gaming'],
                    'signup' => false,
                    'pets' => [],
                ],
            ],
        ];

        $builder = new FormBuilder('test', $data);

        $builder->add('people', CollectionType::class, [
            'entry_type' => PersonType::class,
        ]);

        $builder->add('send_message', 'submit');

        $form = $builder->getForm();
        $form->render();

        $formPeople = $form->get('people')->getData();

        $this->assertSame($data['people'], $formPeople);
    }

    public function testCollectionTwoWayDataBinding(): void
    {
        $data = new \ArrayObject([
            'people' => [
                [
                    'name' => 'John Smith',
                    'emailAddress' => 'john.smith@example.org',
                    'age' => 42,
                    'favouriteConsole' => 'PlayStation',
                    'interests' => ['football', 'gaming'],
                    'signup' => false,
                    'pets' => [],
                ],
            ],
        ]);

        $builder = new FormBuilder('test', $data);

        $builder->add('people', CollectionType::class, [
            'entry_type' => PersonType::class,
        ]);

        $builder->add('send_message', 'submit');

        $form = $builder->getForm();

        $form->submit([
            'people' => [
                [
                    'name' => 'Bob Smith',
                    'emailAddress' => 'bob.smith@example.org',
                    'age' => 45,
                    'favouriteConsole' => 'Xbox',
                    'interests' => ['guitar'],
                ],
            ],
        ]);

        $this->assertSame('Bob Smith', $data['people'][0]['name']);
        $this->assertSame('bob.smith@example.org', $data['people'][0]['emailAddress']);
        $this->assertSame(45, $data['people'][0]['age']);
        $this->assertSame('Xbox', $data['people'][0]['favouriteConsole']);
        $this->assertSame(['guitar'], $data['people'][0]['interests']);
    }

    private function buildForm(\ArrayAccess|array|Person|\stdClass $person): Form
    {
        $builder = new FormBuilder([], $person);

        $builder
            ->add('name', 'text')
            ->add('emailAddress', 'text')
            ->add('age', 'integer')
            ->add('signup', 'checkbox', [
                'required' => false,
            ])
            ->add('favouriteConsole', 'choice', [
                'placeholder' => 'Select a console',
                'choices' => [
                    'PlayStation' => 'PlayStation',
                    'Xbox' => 'Xbox',
                    'Switch' => 'Switch',
                ],
            ])
            ->add('interests', 'choice', [
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'football' => 'Football',
                    'gaming' => 'Gaming',
                    'music' => 'Music',
                ],
            ])
            ->add('pets', 'collection', [
                'entry_type' => 'text',
            ])
        ;

        return $builder->getForm();
    }
}
