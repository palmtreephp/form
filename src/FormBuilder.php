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
    /** @var array */
    private $types;

    public function __construct(array $args = [])
    {
        $this->findTypeClasses();
        $this->form = new Form($args);
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $args
     *
     * @return FormBuilder
     */
    public function add($name, $type = TextType::class, $args = [])
    {
        if ($this->getTypeClass($type) === RepeatedType::class || $type instanceof RepeatedType) {
            return $this->addRepeatedType($name, $type, $args);
        }

        $formControl = $this->getTypeObject($type, $args);

        if (!isset($args['name'])) {
            $formControl->setName($name);
        }

        if ($formControl->getLabel() === null) {
            $formControl->setLabel($formControl->getHumanName());
        }

        $this->getForm()->add($formControl);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return AbstractType
     */
    public function get($name)
    {
        return $this->getForm()->get($name);
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function getTypeClass($type)
    {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        if (\class_exists($type)) {
            return $type;
        }

        return null;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    private function addRepeatedType($name, $type, $args)
    {
        /** @var RepeatedType $typeObject */
        $typeObject = $this->getTypeObject($type, $args);

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

    /**
     * Returns a new instance of the given form type.
     *
     * @param string $type FQCN of the form type or short hand e.g 'text', 'email'.
     * @param array  $args Arguments to pass to the type class constructor.
     *
     * @return AbstractType
     */
    private function getTypeObject($type, $args)
    {
        /* @var AbstractType $object */
        if ($type instanceof AbstractType) {
            $object = $type;
        } else {
            $class = $this->getTypeClass($type);

            if (!\class_exists($class)) {
                $class = TextType::class;
            }

            $object = new $class($args, $this);
        }

        return $object;
    }

    private function findTypeClasses()
    {
        if ($this->types === null) {
            $this->types = [];
            $namespace   = __NAMESPACE__ . '\\Type';

            $files = new \GlobIterator(__DIR__ . '/Type/*Type.php');

            foreach ($files as $file) {
                $class = \basename($file, '.php');
                $type  = \basename($file, 'Type.php');

                $this->types[\strtolower($type)] = $namespace . '\\' . $class;
            }
        }
    }
}
