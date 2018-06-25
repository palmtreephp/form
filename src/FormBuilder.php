<?php

namespace Palmtree\Form;

use Palmtree\Form\Constraint\Match;
use Palmtree\Form\Type\AbstractType;
use Palmtree\Form\Type\RepeatedType;
use Palmtree\Form\Type\TextType;

class FormBuilder
{
    /** @var Form */
    protected $form;
    /** @var array */
    public static $types;

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
        } else {
            $formControl = $this->getTypeObject($type, $args);

            if (!array_key_exists('name', $args)) {
                $formControl->setName($name);
            }

            if ($formControl->getLabel() === null) {
                $formControl->setLabel($formControl->getHumanName());
            }

            $this->getForm()->addField($formControl);

            return $this;
        }
    }

    /**
     * @param string $name
     *
     * @return AbstractType
     */
    public function get($name)
    {
        return $this->getForm()->getField($name);
    }

    protected function addRepeatedType($name, $type, $args)
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
    protected function getTypeObject($type, $args)
    {
        /** @var AbstractType $object */
        if ($type instanceof AbstractType) {
            $object = $type;
        } else {
            $class = $this->getTypeClass($type);

            if (!class_exists($class)) {
                $class = TextType::class;
            }

            $object = new $class($args, $this);
        }

        return $object;
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function getTypeClass($type)
    {
        if (isset(self::$types[$type])) {
            return self::$types[$type];
        }

        if (class_exists($type)) {
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

    /**
     *
     */
    private function findTypeClasses()
    {
        if (self::$types === null) {
            self::$types = [];
            $namespace   = __NAMESPACE__ . '\\Type';

            $files = new \GlobIterator(__DIR__ . '/Type/*Type.php');

            foreach ($files as $file) {
                $class = basename($file, '.php');
                $type  = basename($file, 'Type.php');

                self::$types[strtolower($type)] = $namespace . '\\' . $class;
            }
        }
    }

}
