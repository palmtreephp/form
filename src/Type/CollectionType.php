<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Form\Exception\InvalidTypeException;
use Palmtree\Form\UploadedFile;
use Palmtree\Html\Element;

/**
 * @phpstan-import-type UploadedFileArray from UploadedFile
 */
class CollectionType extends AbstractType
{
    protected ?string $errorMessage = null;
    protected bool $required = false;
    protected ?string $label = '';
    /** @var class-string<TypeInterface> */
    private string $entryType;
    /** @var array<string, mixed> */
    private array $entryOptions = [];
    private ?string $addLabel = null;
    private ?int $maxEntries = null;
    private ?int $minEntries = null;

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

        $config = array_filter([
            'addLabel' => $this->addLabel,
            'minEntries' => $this->minEntries,
            'maxEntries' => $this->maxEntries,
        ], fn ($value) => $value !== null);

        $collectionWrapper->attributes->setData('palmtree-form-collection', htmlentities(json_encode($config)));

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
     * @param class-string<TypeInterface>|string $entryType
     */
    public function setEntryType(string $entryType): void
    {
        if (!$class = $this->typeLocator->getTypeClass($entryType)) {
            throw new InvalidTypeException('Type could not be found');
        }

        $this->entryType = $class;
    }

    /**
     * @return class-string<TypeInterface>
     */
    public function getEntryType(): string
    {
        return $this->entryType;
    }

    /**
     * @param array<string, mixed> $entryOptions
     */
    public function setEntryOptions(array $entryOptions): void
    {
        $this->entryOptions = $entryOptions;
    }

    /**
     * @return array<string, mixed>
     */
    public function getEntryOptions(): array
    {
        return $this->entryOptions;
    }

    public function setData(array|string|int|bool|null $data): TypeInterface
    {
        $data = (array)$data;

        if ($this->entryType === FileType::class) {
            $data = self::normalizeFilesArray($data);
        }

        $this->data = $data;

        return $this;
    }

    /**
     * @return list<mixed>
     */
    public function getNormData(): array
    {
        $normData = [];
        foreach ($this->all() as $child) {
            $normData[] = $child->getNormData();
        }

        return $normData;
    }

    /**
     * @return list<mixed>
     */
    public function getData(): array
    {
        $data = [];
        foreach ($this->all() as $child) {
            $data[] = $child->getData();
        }

        return $data;
    }

    public function setMinEntries(int $minEntries): void
    {
        $this->minEntries = $minEntries;
    }

    public function setMaxEntries(int $maxEntries): void
    {
        $this->maxEntries = $maxEntries;
    }

    public function setAddLabel(string $addLabel): void
    {
        $this->addLabel = $addLabel;
    }

    private function buildEntry(int $position = 0, mixed $data = null): TypeInterface
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

        $html = trim((string)preg_replace('/>\s+</', '><', $prototype->render()));

        return htmlentities($html);
    }

    private static function clearPrototypeEntryConstraints(TypeInterface $entry): void
    {
        $entry->clearConstraints();

        foreach ($entry->all() as $child) {
            self::clearPrototypeEntryConstraints($child);
        }
    }

    /**
     * @param array<key-of<UploadedFileArray>, array<string>> $data
     *
     * @return array<UploadedFileArray>
     */
    private static function normalizeFilesArray(array $data): array
    {
        $normalized = [];
        $keys = array_keys($data);

        for ($i = 0, $total = \count($data['name']); $i < $total; ++$i) {
            foreach ($keys as $key) {
                $normalized[$i][$key] = $data[$key][$i];
            }
        }

        /** @var array<UploadedFileArray> */
        return $normalized;
    }
}
