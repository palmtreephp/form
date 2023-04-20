<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Form;

trait ObjectDataMapper
{
    public function mapDataToForm(Form $form): void
    {
        foreach ($form->all() as $child) {
            $name = $child->getName();

            $child->setData($this->getPropertyValue($name));
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
        $getter = 'get' . ucfirst($property);

        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
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
        }
    }
}
