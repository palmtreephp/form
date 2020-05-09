<?php

namespace Palmtree\Form;

use Palmtree\Form\Type\RepeatedType;
use Palmtree\Form\Type\TextType;
use Palmtree\Form\Type\TypeInterface;

class FormBuilder
{
    /** @var Form */
    private $form;
    /** @var TypeLocator */
    private $typeLocator;
    /** @var RepeatedTypeBuilder */
    private $repeatedTypeBuilder;

    public function __construct(array $args = [])
    {
        $this->form        = new Form($args);
        $this->typeLocator = new TypeLocator();
    }

    /**
     * Creates a form field and returns the current instance of the FormBuilder for chaining.
     *
     * @param string|object $type
     */
    public function add(string $name, $type = TextType::class, array $args = []): self
    {
        $this->create($name, $type, $args);

        return $this;
    }

    /**
     * Creates and returns a form field.
     *
     * @param string|object $type
     */
    public function create(string $name, $type = TextType::class, array $args = []): TypeInterface
    {
        if ((\is_object($type) && $type instanceof RepeatedType) || $this->typeLocator->getTypeClass($type) === RepeatedType::class) {
            return $this->getRepeatedTypeBuilder()->build($name, $args);
        }

        $fieldType = $this->typeLocator->getTypeObject($type, $args);

        if (!isset($args['name'])) {
            $fieldType->setName($name);
        }

        if ($fieldType->getLabel() === null) {
            $fieldType->setLabel($fieldType->getHumanName());
        }

        $this->form->add($fieldType);

        return $fieldType;
    }

    public function get(string $name): ?TypeInterface
    {
        return $this->form->get($name);
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function enableFileUploads(): self
    {
        $this->form->setEncType('multipart/form-data');

        return $this;
    }

    private function getRepeatedTypeBuilder(): RepeatedTypeBuilder
    {
        if (!$this->repeatedTypeBuilder) {
            $this->repeatedTypeBuilder = new RepeatedTypeBuilder($this);
        }

        return $this->repeatedTypeBuilder;
    }
}
