<?php

namespace Palmtree\Form;

use Palmtree\Form\Type\AbstractType;
use Palmtree\Form\Type\TextType;
use Palmtree\NameConverter\SnakeCaseToHumanNameConverter;

class FormBuilder
{
    protected $form;
    public static $types;

    public function __construct($args = [])
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
        $formControl = $this->getTypeObject($type, $args);

        if (!array_key_exists('name', $args)) {
            $formControl->setName($name);
        }

        if ($formControl->getLabel() === null) {
            $humanName = (new SnakeCaseToHumanNameConverter())->normalize($name);
            $formControl->setLabel($humanName);
        }

        $this->getForm()->addField($formControl);

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

            $object = new $class($args);
        }

        return $object;
    }

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
