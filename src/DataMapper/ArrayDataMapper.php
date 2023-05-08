<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Exception\OutOfBoundsException;
use Palmtree\Form\Form;

class ArrayDataMapper implements DataMapperInterface
{
    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param array|\ArrayAccess $data
     */
    public function mapDataToForm($data, Form $form): void
    {
        foreach ($form->allMapped() as $child) {
            if (!$this->keyExists($child->getName(), $data)) {
                $this->throwOutOfBoundsException($child->getName(), $data);
            }

            $child->setData($data[$child->getName()]);
        }
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param array|\ArrayAccess $data
     */
    public function mapDataFromForm($data, array $formData, Form $form): void
    {
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

    /**
     * @param array|\ArrayAccess $data
     */
    private function keyExists(string $key, $data): bool
    {
        if (\is_array($data)) {
            return \array_key_exists($key, $data);
        }

        if ($data instanceof \ArrayAccess) {
            return $data->offsetExists($key);
        }

        throw new \InvalidArgumentException('Data must be an array or implement \ArrayAccess');
    }
}
