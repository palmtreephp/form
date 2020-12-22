<?php

namespace Palmtree\Form;

use Palmtree\Form\Constraint\Match;
use Palmtree\Form\Type\AbstractType;
use Palmtree\Form\Type\CollectionType;
use Palmtree\Form\Type\FileType;
use Palmtree\Form\Type\RepeatedType;
use Palmtree\Form\Type\TextType;

class FormBuilder
{
    /** @var Form */
    private $form;
    /** @var TypeLocator */
    private $typeLocator;

    private const FILE_UPLOAD_ENC_TYPE = 'multipart/form-data';

    public function __construct(array $args = [])
    {
        $this->form        = new Form($args);
        $this->typeLocator = new TypeLocator();
    }

    /**
     * @param string|object $type
     */
    public function add(string $name, $type = TextType::class, array $args = []): self
    {
        if ((\is_object($type) && $type instanceof RepeatedType) || $this->typeLocator->getTypeClass($type) === RepeatedType::class) {
            return $this->addRepeatedType($name, $type, $args);
        }

        $formControl = $this->typeLocator->getTypeObject($type, $args);

        if (!isset($args['name'])) {
            $formControl->setName($name);
        }

        if ($formControl->getLabel() === null) {
            $formControl->setLabel($formControl->getHumanName());
        }

        $this->form->add($formControl);

        if ($formControl instanceof FileType || ($formControl instanceof CollectionType && $formControl->getEntryType() === FileType::class)) {
            $this->form->setEncType(self::FILE_UPLOAD_ENC_TYPE);
        }

        $this->recursiveEncTypeCheck($formControl->getChildren());

        return $this;
    }

    public function get(string $name): ?AbstractType
    {
        return $this->form->get($name);
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    private function addRepeatedType(string $name, string $type, array $args): self
    {
        /** @var RepeatedType $typeObject */
        $typeObject = $this->typeLocator->getTypeObject($type, $args);

        $this->add($name, $typeObject->getRepeatableType(), $args);

        $firstOfType = $this->get($name);

        $secondArgs = $args;

        if (!isset($secondArgs['name'])) {
            $secondArgs['name'] = $firstOfType->getName() . '_2';
        }

        if (!isset($secondArgs['label'])) {
            $secondArgs['label'] = 'Confirm ' . $firstOfType->getLabel();
        }

        if (!isset($secondArgs['placeholder'])) {
            $secondArgs['placeholder'] = $firstOfType->getPlaceHolderAttribute() . ' again';
        }

        $this->add($secondArgs['name'], $typeObject->getRepeatableType(), $secondArgs);

        $secondOfType = $this->get($secondArgs['name']);

        $matchConstraint = new Match([
            'match_field'   => $secondOfType,
            'error_message' => $firstOfType->getHumanName() . 's do not match',
        ]);

        $firstOfType->addConstraint($matchConstraint);

        $secondMatchConstraint = clone $matchConstraint;
        $secondMatchConstraint->setMatchField($firstOfType);

        $secondOfType->addConstraint($secondMatchConstraint);

        return $this;
    }

    /** @var AbstractType[] $formControls */
    private function recursiveEncTypeCheck($formControls)
    {
        if ($this->form->getEncType() === self::FILE_UPLOAD_ENC_TYPE) {
            return;
        }

        foreach ($formControls as $formControl) {
            if ($formControl instanceof FileType) {
                $this->form->setEncType(self::FILE_UPLOAD_ENC_TYPE);
                return;
            }

            if ($children = $formControl->getChildren()) {
                $this->recursiveEncTypeCheck($children);
            }
        }
    }
}
