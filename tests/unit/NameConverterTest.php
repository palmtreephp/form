<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\FormBuilder;
use Palmtree\NameConverter\CamelCaseToHumanNameConverter;
use PHPUnit\Framework\TestCase;

class NameConverterTest extends TestCase
{
    public function testNameConverterPassedToTypeObjects(): void
    {
        $customNameConverter = new CamelCaseToHumanNameConverter();

        $builder = new FormBuilder();
        $builder->setNameConverter($customNameConverter);

        // Add fields to the form
        $builder->add('firstName', 'text');
        $builder->add('lastName', 'text');
        $builder->add('emailAddress', 'text');

        // Get the created type objects
        $firstNameField = $builder->get('firstName');
        $lastNameField = $builder->get('lastName');
        $emailAddressField = $builder->get('emailAddress');

        // Verify that the custom name converter was passed to each field
        // by checking that they use it to generate human names
        $this->assertSame('First Name', $firstNameField->getHumanName());
        $this->assertSame('Last Name', $lastNameField->getHumanName());
        $this->assertSame('Email Address', $emailAddressField->getHumanName());
    }

    public function testDefaultNameConverterIsSnakeCaseToHuman(): void
    {
        $builder = new FormBuilder();

        // Add fields to the form without setting a custom name converter
        $builder->add('first_name', 'text');
        $builder->add('last_name', 'text');
        $builder->add('email_address', 'text');

        // Get the created type objects
        $firstNameField = $builder->get('first_name');
        $lastNameField = $builder->get('last_name');
        $emailAddressField = $builder->get('email_address');

        // Verify that the default SnakeCaseToHumanNameConverter is used
        $this->assertSame('First Name', $firstNameField->getHumanName());
        $this->assertSame('Last Name', $lastNameField->getHumanName());
        $this->assertSame('Email Address', $emailAddressField->getHumanName());
    }
}
