<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class CollectionType extends AbstractType
{
    /** @var string */
    private $entryType;
    /** @var array */
    private $entryOptions = [];

    public function getElement()
    {
        $collectionWrapper = new Element('div.palmtree-form-collection');

        $entriesWrapper = new Element('div.palmtree-form-collection-entries');

        $collectionWrapper->addChild($entriesWrapper);

        if ($this->getData()) {
            foreach ($this->getData() as $key => $value) {
                $entriesWrapper->addChild($this->buildEntry($value));
            }
        } else {
            $entriesWrapper->addChild($this->buildEntry());
        }

        return $collectionWrapper;
    }

    private function buildEntry($data = null)
    {
        $entryType = $this->entryType;
        /** @var AbstractType $entry */
        $entry = new $entryType($this->getEntryOptions());
        $entry
            ->setParent($this)
            ->setForm($this->getForm())
            ->setName($this->getName());

        if (\func_num_args() > 0) {
            $entry->setData($data);
        }

        $entryWrapper = new Element('div.palmtree-form-collection-entry');
        foreach ($entry->getElements() as $element) {
            $entryWrapper->addChild($element);
        }

        return $entryWrapper;
    }

    /**
     * @param string $entryType
     */
    public function setEntryType($entryType)
    {
        $this->entryType = $entryType;
    }

    /**
     * @return string
     */
    public function getEntryType()
    {
        return $this->entryType;
    }

    /**
     * @param array $entryOptions
     */
    public function setEntryOptions($entryOptions)
    {
        $this->entryOptions = $entryOptions;
    }

    /**
     * @return array
     */
    public function getEntryOptions()
    {
        return $this->entryOptions;
    }

    /**
     * @param array $data
     *
     * @return AbstractType
     */
    public function setData($data)
    {
        if ($this->getEntryType() === FileType::class) {
            $data = self::normalizeFilesArray($data);
        }

        $this->data = $data;

        return $this;
    }

    private static function normalizeFilesArray($data)
    {
        $normalized = [];
        $keys       = \array_keys($data);

        for ($i = 0, $total = \count($data['name']); $i < $total; ++$i) {
            foreach ($keys as $key) {
                $normalized[$i][$key] = $data[$key][$i];
            }
        }

        return $normalized;
    }
}
