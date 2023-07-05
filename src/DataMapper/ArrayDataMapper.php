<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Exception\OutOfBoundsException;
use Palmtree\Form\Form;

class ArrayDataMapper implements DataMapperInterface
{
    public function mapDataToForm(object|array $data, Form $form): void
    {
        if (\is_object($data) && !$data instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('Data must be an array or implement \ArrayAccess');
        }

        foreach ($form->allMapped() as $child) {
            if (!$this->keyExists($child->getName(), $data)) {
                $this->throwOutOfBoundsException($child->getName(), $data);
            }

            $child->setData($data[$child->getName()]);
        }
    }

    public function mapDataFromForm(object|array $data, array $formData, Form $form): void
    {
        if (\is_object($data) && !$data instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('Data must be an array or implement \ArrayAccess');
        }

        foreach ($formData as $key => $value) {
            if (!$this->keyExists($key, $data)) {
                $this->throwOutOfBoundsException($key, $data);
            }

            $data[$key] = $value;
        }
    }

    /**
     * @param array|\ArrayAccess $data
     */
    private function throwOutOfBoundsException(string $key, $data): void
    {
        $format = "Key '%s' not found in bound data";
        $params = [$key];

        if ($keys = $this->keys($data)) {
            $format .= ' with the following keys: %s';
            $params[] = implode(', ', $keys);
        }

        throw new OutOfBoundsException(vsprintf($format, $params));
    }

    /**
     * @param array|\ArrayAccess $data
     */
    private function keys($data): ?array
    {
        if (\is_array($data)) {
            return array_keys($data);
        }

        if ($data instanceof \ArrayObject) {
            return array_keys($data->getArrayCopy());
        }

        if ($data instanceof \Traversable) {
            return array_keys(iterator_to_array($data));
        }

        return null;
    }

    private function keyExists(string $key, array|\ArrayAccess $data): bool
    {
        if ($data instanceof \ArrayAccess) {
            return $data->offsetExists($key);
        }

        return \array_key_exists($key, $data);
    }
}
