<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Form\Exception\InvalidTypeException;
use Palmtree\Html\Element;

class CollectionType extends AbstractType
{
    /** @var string|null */
    protected $errorMessage;
    /** @var bool */
    protected $required = false;
    /** @var string|null */
    protected $label = '';
    /** @var class-string<TypeInterface> */
    private $entryType;
    /** @var array */
    private $entryOptions = [];

    public function getElement(): Element
    {
        $collectionWrapper = new Element('div.palmtree-form-collection');
        $collectionWrapper->classes->add(...$this->args['classes'] ?? []);
        $collectionWrapper->attributes['id'] = $this->getIdAttribute();

        $entriesWrapper = new Element('div.palmtree-form-collection-entries');

        $collectionWrapper->addChild($entriesWrapper);

        $this->build();

        foreach ($this->children as $entry) {
            $entriesWrapper->addChild($this->buildEntryElement($entry));
        }

        $collectionWrapper->attributes->setData('prototype', $this->generatePrototype());

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

    public function addChild(TypeInterface $child): TypeInterface
    {
        if ($child->getParent() !== $this) {
            $child->setParent($this);
        }

        $this->children[$child->getPosition()] = $child;

        return $this;
    }

    /**
     * @psalm-param class-string<TypeInterface>|string $entryType
     */
    public function setEntryType(string $entryType): void
    {
        if (!$class = $this->typeLocator->getTypeClass($entryType)) {
            throw new InvalidTypeException('Type could not be found');
        }

        $this->entryType = $class;
    }

    /**
     * @psalm-return class-string<TypeInterface>
     */
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

    public function setData($data): TypeInterface
    {
        $data = (array)$data;

        if ($this->entryType === FileType::class) {
            $data = self::normalizeFilesArray($data);
        }

        $this->data = $data;

        return $this;
    }

    public function getNormData(): array
    {
        $normData = [];
        foreach ($this->all() as $child) {
            $normData[] = $child->getNormData();
        }

        return $normData;
    }

    public function getData(): array
    {
        $data = [];
        foreach ($this->all() as $child) {
            $data[] = $child->getData();
        }

        return $data;
    }

    /**
     * @param mixed $data
     */
    private function buildEntry(int $position = 0, $data = null): TypeInterface
    {
        $entryType = $this->entryType;
        /** @var TypeInterface $entry */
        $entry = new $entryType($this->entryOptions);
        $entry
            ->setParent($this)
            ->setName($this->name)
            ->setPosition($position)
        ;

        if ($entry->getLabel() === null) {
            $entry->setLabel($this->getHumanName());
        }

        $entry->build();

        if (\func_num_args() > 0) {
            $entry->setData($data);

            if (\is_array($data)) {
                foreach ($entry->all() as $child) {
                    if (isset($data[$child->getName()])) {
                        $child->setData($data[$child->getName()]);
                    }
                }
            }
        }

        return $entry;
    }

    private function buildEntryElement(TypeInterface $entry): Element
    {
        $entryWrapper = new Element('div.palmtree-form-collection-entry');
        foreach ($entry->getElements() as $element) {
            $entryWrapper->addChild($element);
        }

        return $entryWrapper;
    }

    private function generatePrototype(): string
    {
        $entry = $this->buildEntry(-1);
        self::clearPrototypeEntryConstraints($entry);

        $prototype = $this->buildEntryElement($entry);

        $html = trim(preg_replace('/>\s+</', '><', $prototype->render()));

        return htmlentities($html);
    }

    private static function clearPrototypeEntryConstraints(TypeInterface $entry): void
    {
        $entry->clearConstraints();

        foreach ($entry->all() as $child) {
            self::clearPrototypeEntryConstraints($child);
        }
    }

    private static function normalizeFilesArray(array $data): array
    {
        $normalized = [];
        $keys = array_keys($data);

        for ($i = 0, $total = \count($data['name']); $i < $total; ++$i) {
            foreach ($keys as $key) {
                $normalized[$i][$key] = $data[$key][$i];
            }
        }

        return $normalized;
    }
}
