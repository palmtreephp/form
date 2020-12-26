<?php declare(strict_types=1);

namespace Palmtree\Form;

use Palmtree\Form\Type\CollectionType;
use Palmtree\Form\Type\FileType;
use Palmtree\Form\Type\RepeatedType;
use Palmtree\Form\Type\TextType;
use Palmtree\Form\Type\TypeInterface;

class FormBuilder
{
    /** @var Form */
    private $form;
    /** @var TypeLocator */
    private $typeLocator;
    /** @var RepeatedTypeBuilder|null */
    private $repeatedTypeBuilder = null;

    private const FILE_UPLOAD_ENC_TYPE = 'multipart/form-data';

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
        if ((\is_object($type) && $type instanceof RepeatedType) || (\is_string($type) && $this->typeLocator->getTypeClass($type) === RepeatedType::class)) {
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

        if ($fieldType instanceof FileType || ($fieldType instanceof CollectionType && $fieldType->getEntryType() === FileType::class)) {
            $this->form->setEncType(self::FILE_UPLOAD_ENC_TYPE);
        } else {
            $this->recursiveEncTypeCheck($fieldType->getChildren());
        }

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

    private function getRepeatedTypeBuilder(): RepeatedTypeBuilder
    {
        if ($this->repeatedTypeBuilder === null) {
            $this->repeatedTypeBuilder = new RepeatedTypeBuilder($this);
        }

        return $this->repeatedTypeBuilder;
    }

    /** @var array<TypeInterface> */
    private function recursiveEncTypeCheck(array $fieldTypes): void
    {
        if ($this->form->getEncType() === self::FILE_UPLOAD_ENC_TYPE) {
            return;
        }

        foreach ($fieldTypes as $fieldType) {
            if ($fieldType instanceof FileType) {
                $this->form->setEncType(self::FILE_UPLOAD_ENC_TYPE);

                return;
            }

            if ($children = $fieldType->getChildren()) {
                $this->recursiveEncTypeCheck($children);
            }
        }
    }
}
