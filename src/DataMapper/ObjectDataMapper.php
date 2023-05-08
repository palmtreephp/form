<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Exception\InaccessiblePropertyException;
use Palmtree\Form\Exception\UnsettablePropertyException;
use Palmtree\Form\Form;

class ObjectDataMapper implements DataMapperInterface
{
    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param object $data
     */
    public function mapDataToForm($data, Form $form): void
    {
        foreach ($form->allMapped() as $child) {
            $child->setData($this->getPropertyValue($data, $child->getName()));
        }
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param object $data
     */
    public function mapDataFromForm($data, array $formData, Form $form): void
    {
        foreach ($formData as $key => $value) {
            $this->setPropertyValue($data, $key, $value);
        }
    }

    /**
     * Gets a property value from the object, prioritizing a getter and isser methods if they exist.
     *
     * @param object $object
     *
     * @return mixed
     */
    private function getPropertyValue($object, string $property)
    {
        $ucFirstProperty = ucfirst($property);
        $getter = 'get' . $ucFirstProperty;

        if (method_exists($object, $getter)) {
            return $object->$getter();
        }

        $isser = 'is' . $ucFirstProperty;

        if (method_exists($object, $isser)) {
            return $object->$isser();
        }

        if (property_exists($object, $property)) {
            return $object->$property;
        }

        throw new InaccessiblePropertyException($property, $object);
    }

    /**
     * Sets a property value on the object, prioritizing a setter method if it exists.
     *
     * @param object $object
     * @param mixed  $value
     */
    private function setPropertyValue($object, string $property, $value): void
    {
        $setter = 'set' . ucfirst($property);

        if (method_exists($object, $setter)) {
            $object->$setter($value);

            return;
        }

        if (property_exists($object, $property)) {
            $object->$property = $value;

            return;
        }

        throw new UnsettablePropertyException($property, $object);
    }
}
