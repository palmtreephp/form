<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Exception\InaccessiblePropertyException;
use Palmtree\Form\Exception\UnsettablePropertyException;
use Palmtree\Form\Form;

class ObjectDataMapper implements DataMapperInterface
{
    public function mapDataToForm(object|array $data, Form $form): void
    {
        if (!\is_object($data)) {
            throw new \InvalidArgumentException('Data must be an object');
        }

        foreach ($form->allMapped() as $child) {
            $child->setData($this->getPropertyValue($data, $child->getName()));
        }
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function mapDataFromForm(object|array $data, array $formData, Form $form): void
    {
        if (!\is_object($data)) {
            throw new \InvalidArgumentException('Data must be an object');
        }

        foreach ($formData as $key => $value) {
            $this->setPropertyValue($data, $key, $value);
        }
    }

    /**
     * Gets a property value from the object, prioritizing a getter and isser methods if they exist.
     */
    private function getPropertyValue(object $object, string $property): mixed
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
     */
    private function setPropertyValue(object $object, string $property, mixed $value): void
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
