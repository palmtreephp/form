<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Exception\InaccessiblePropertyException;
use Palmtree\Form\Exception\UnsettablePropertyException;
use Palmtree\Form\Form;

trait ObjectDataMapper
{
    public function mapDataToForm(Form $form): void
    {
        foreach ($form->allMapped() as $child) {
            if ($child->isMapped()) {
                $name = $child->getName();

                $child->setData($this->getPropertyValue($name));
            }
        }
    }

    public function mapDataFromForm(array $data, Form $form): void
    {
        foreach ($data as $key => $value) {
            $this->setPropertyValue($key, $value);
        }
    }

    private function getPropertyValue(string $property)
    {
        $ucFirstProperty = ucfirst($property);
        $getter = 'get' . $ucFirstProperty;

        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        $isser = 'is' . $ucFirstProperty;

        if (method_exists($this, $isser)) {
            return $this->$isser();
        }

        if (property_exists($this, $property)) {
            return $this->$property;
        }

        throw new InaccessiblePropertyException($property, $this);
    }

    private function setPropertyValue(string $property, $value): void
    {
        $setter = 'set' . ucfirst($property);

        if (method_exists($this, $setter)) {
            $this->$setter($value);

            return;
        }

        if (property_exists($this, $property)) {
            $this->$property = $value;

            return;
        }

        throw new UnsettablePropertyException($property, $this);
    }
}
