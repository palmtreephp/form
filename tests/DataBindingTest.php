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

        $this->assertSame($person->name, 'John Smith');
        $this->assertSame($person->emailAddress, 'john.smith@example.org');
        $this->assertSame($person->getAge(), 42);
        $this->assertSame($person->getFavouriteConsole(), 'PlayStation');
        $this->assertSame($person->interests, ['football', 'gaming']);
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

        $this->assertSame($form->get('name')->getData(), 'John Smith');
        $this->assertSame($form->get('emailAddress')->getData(), 'john.smith@example.org');
        $this->assertSame($form->get('age')->getData(), 42);
        $this->assertSame($form->get('favouriteConsole')->getData(), 'PlayStation');
        $this->assertSame($form->get('interests')->getData(), ['football', 'gaming']);
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

        $this->assertSame($form->get('name')->getData(), 'John Smith');
        $this->assertSame($form->get('emailAddress')->getData(), 'john.smith@example.org');
        $this->assertSame($form->get('age')->getData(), 42);
        $this->assertSame($form->get('favouriteConsole')->getData(), 'PlayStation');
        $this->assertSame($form->get('interests')->getData(), ['football', 'gaming']);

        $form->submit([
            'name' => 'Bob Smith',
            'emailAddress' => 'bob.smith@example.org',
            'age' => 45,
            'favouriteConsole' => 'Xbox',
            'interests' => ['guitar'],
        ]);

        $this->assertSame($person['name'], 'Bob Smith');
        $this->assertSame($person['emailAddress'], 'bob.smith@example.org');
        $this->assertSame($person['age'], 45);
        $this->assertSame($person['favouriteConsole'], 'Xbox');
        $this->assertSame($person['interests'], ['guitar']);
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

        $this->assertSame($form->get('name')->getData(), 'John Smith');
        $this->assertSame($form->get('emailAddress')->getData(), 'john.smith@example.org');
        $this->assertSame($form->get('age')->getData(), 42);
        $this->assertSame($form->get('favouriteConsole')->getData(), 'PlayStation');
        $this->assertSame($form->get('interests')->getData(), ['football', 'gaming']);

        $form->submit([
            'name' => 'Bob Smith',
            'emailAddress' => 'bob.smith@example.org',
            'age' => 45,
            'favouriteConsole' => 'Xbox',
            'interests' => ['guitar'],
        ]);

        $this->assertSame($person->name, 'Bob Smith');
        $this->assertSame($person->emailAddress, 'bob.smith@example.org');
        $this->assertSame($person->age, 45);
        $this->assertSame($person->favouriteConsole, 'Xbox');
        $this->assertSame($person->interests, ['guitar']);
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

        $this->assertSame($data['people'][0]['name'], 'Bob Smith');
        $this->assertSame($data['people'][0]['emailAddress'], 'bob.smith@example.org');
        $this->assertSame($data['people'][0]['age'], 45);
        $this->assertSame($data['people'][0]['favouriteConsole'], 'Xbox');
        $this->assertSame($data['people'][0]['interests'], ['guitar']);
    }

    /**
     * @param Person|array|\ArrayAccess|\stdClass $person
     */
    private function buildForm($person): Form
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
