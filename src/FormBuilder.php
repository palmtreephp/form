<?php

namespace Palmtree\Form;

use Palmtree\NameConverter\SnakeCaseToHumanNameConverter;
use Palmtree\Form\Type\AbstractType;
use Palmtree\Form\Type\TextType;

class FormBuilder
{
    protected $form;
    public static $types;

    public function __construct($args = [])
    {
        $this->findTypeClasses();
        $this->form = new Form($args);
    }

    public function add($name, $type = TextType::class, $args = [])
    {
        $formControl = $this->getObject($type, $args);

        if (!array_key_exists('name', $args)) {
            $formControl->setName($name);
        }

        $humanName = (new SnakeCaseToHumanNameConverter())->normalize($name);

        if ($formControl->getLabel() === null) {
            $formControl->setLabel($humanName);
        }

        $this->getForm()->addField($formControl);

        return $this;
    }

    protected function getObject($type, $args)
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
        if (array_key_exists($type, self::$types)) {
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
            $files       = glob(__DIR__ . '/Type/*Type.php');

            if ($files) {
                foreach ($files as $file) {
                    $class = basename($file, '.php');
                    $type  = basename($file, 'Type.php');

                    self::$types[strtolower($type)] = $namespace . '\\' . $class;
                }
            }
        }
    }

}
