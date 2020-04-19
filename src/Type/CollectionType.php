<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class CollectionType extends AbstractType
{
    /** @var string */
    private $entryType;
    /** @var array */
    private $entryOptions = [];

    public function getElement(): Element
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

        $collectionWrapper->attributes->setData('prototype', htmlentities($prototype->render()));

        return $collectionWrapper;
    }

    public function build(): void
    {
        if (\is_array($this->data)) {
            foreach ($this->data as $key => $value) {
                $this->addChild($this->buildEntry($key, $value));
            }
        }
    }

    private function buildEntry($position = 0, ?array $data = null): AbstractType
    {
        $entryType = $this->entryType;
        /** @var AbstractType $entry */
        $entry = new $entryType($this->entryOptions);
        $entry
            ->setParent($this)
            ->setName($this->name)
            ->setPosition($position);

        $entry->build();

        if (\func_num_args() > 0) {
            $entry->setData($data);
        }

        return $entry;
    }

    private function buildEntryElement(AbstractType $entry): Element
    {
        $entryWrapper = new Element('div.palmtree-form-collection-entry');
        foreach ($entry->getElements() as $element) {
            $entryWrapper->addChild($element);
        }

        return $entryWrapper;
    }

    private function clearPrototypeEntryConstraints(AbstractType $entry): void
    {
        $entry->setConstraints([]);

        foreach ($entry->getChildren() as $child) {
            $this->clearPrototypeEntryConstraints($child);
        }
    }

    public function addChild(AbstractType $child): AbstractType
    {
        if ($child->getParent() !== $this) {
            $child->setParent($this);
        }

        $this->children[$child->getPosition()] = $child;

        return $this;
    }

    public function setEntryType(string $entryType): void
    {
        $this->entryType = $entryType;
    }

    public function getEntryType(): string
    {
        return $this->entryType;
    }

    public function setEntryOptions(array $entryOptions): void
    {
        $this->entryOptions = $entryOptions;
    }

    public function getEntryOptions(): array
    {
        return $this->entryOptions;
    }

    /**
     * @param array $data
     */
    public function setData($data): AbstractType
    {
        if ($this->getEntryType() === FileType::class) {
            $data = self::normalizeFilesArray($data);
        }

        $this->data = $data;

        return $this;
    }

    private static function normalizeFilesArray(array $data): array
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
