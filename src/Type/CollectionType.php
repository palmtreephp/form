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

        if ($entries = $this->getChildren()) {
            foreach ($entries as $entry) {
                $entriesWrapper->addChild($this->buildEntryElement($entry));
            }
        } else {
            $entriesWrapper->addChild($this->buildEntryElement($this->buildEntry()));
        }

        $prototypeEntry = $this->buildEntry('__name__');

        $this->clearPrototypeEntryConstraints($prototypeEntry);

        $prototype = $this->buildEntryElement($prototypeEntry);

        $collectionWrapper->addDataAttribute('prototype', htmlentities($prototype->render()));

        return $collectionWrapper;
    }

    public function build()
    {
        if ($data = $this->getData()) {
            foreach ($data as $key => $value) {
                $this->addChild($this->buildEntry($key, $value));
            }
        }
    }

    private function buildEntry($position = 0, $data = null)
    {
        $entryType = $this->entryType;
        /** @var AbstractType $entry */
        $entry = new $entryType($this->getEntryOptions());
        $entry
            ->setParent($this)
            ->setName($this->getName())
            ->setPosition($position);

        $entry->build();

        if (\func_num_args() > 0) {
            $entry->setData($data);
        }

        return $entry;
    }

    private function buildEntryElement(AbstractType $entry)
    {
        $entryWrapper = new Element('div.palmtree-form-collection-entry');
        foreach ($entry->getElements() as $element) {
            $entryWrapper->addChild($element);
        }

        return $entryWrapper;
    }

    private function clearPrototypeEntryConstraints(AbstractType $entry)
    {
        $entry->setConstraints([]);

        foreach ($entry->getChildren() as $child) {
            $this->clearPrototypeEntryConstraints($child);
        }
    }

    /**
     * @return self
     */
    public function addChild(AbstractType $child)
    {
        if ($child->getParent() !== $this) {
            $child->setParent($this);
        }

        $this->children[$child->getPosition()] = $child;

        return $this;
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
        $keys       = array_keys($data);

        for ($i = 0, $total = \count($data['name']); $i < $total; ++$i) {
            foreach ($keys as $key) {
                $normalized[$i][$key] = $data[$key][$i];
            }
        }

        return $normalized;
    }
}
