<?php

namespace Palmtree\Form;

use Palmtree\Form\Constraint\Match;
use Palmtree\Form\Type\AbstractType;
use Palmtree\Form\Type\RepeatedType;
use Palmtree\Form\Type\TextType;

class FormBuilder
{
    /** @var Form */
    private $form;
    /** @var TypeLocator */
    private $typeLocator;

    public function __construct(array $args = [])
    {
        $this->form        = new Form($args);
        $this->typeLocator = new TypeLocator();
    }

    public function add(string $name, string $type = TextType::class, array $args = []): self
    {
        if ($type instanceof RepeatedType || $this->typeLocator->getTypeClass($type) === RepeatedType::class) {
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

        /** @var AbstractType $firstOfType */
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

        /** @var AbstractType $secondOfType */
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
}
