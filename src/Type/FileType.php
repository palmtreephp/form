<?php

namespace Palmtree\Form\Type;

use Palmtree\Form\UploadedFile;
use Palmtree\Html\Element;

class FileType extends AbstractType
{
    /** @var string */
    protected $type = 'file';
    /** @var bool */
    private $custom = true;
    /** @var string */
    private $browseText = 'Browse';
    /** @var UploadedFile|null */
    private $normData = null;

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        if ($this->custom && !$this->label) {
            $this->label = 'Choose file...';
        }
    }

    public function getElement(): Element
    {
        $element = parent::getElement();

        unset($element->attributes['value']);

        if ($this->custom) {
            $element->classes[] = 'custom-file-input';
        }

        return $element;
    }

    public function getLabelElement(): ?Element
    {
        $element = parent::getLabelElement();

        if ($element && $this->custom) {
            $element->classes[] = 'custom-file-label';
            $element->attributes->setData('browse', $this->browseText);
        }

        return $element;
    }

    public function getElements()
    {
        $elements = parent::getElements();

        if (!$this->custom) {
            return $elements;
        }

        $customFileWrapper = new Element('div.custom-file');

        // Add input first because label needs to be below the input element in the DOM
        $customFileWrapper->addChild($elements[1]);
        unset($elements[1]);

        $customFileWrapper->addChild(...$elements);

        return [$customFileWrapper];
    }

    public function setCustom(bool $custom): void
    {
        $this->custom = $custom;
    }

    public function isCustom(): bool
    {
        return $this->custom;
    }

    public function setBrowseText(string $browseText): void
    {
        $this->browseText = $browseText;
    }

    public function getBrowseText(): string
    {
        return $this->browseText;
    }

    public function isValid(): bool
    {
        if (!$this->form->isSubmitted()) {
            return true;
        }

        if (!parent::isValid()) {
            return false;
        }

        if (($uploadedFile = $this->getData()) && $uploadedFile->getErrorCode() > 0) {
            $this->setErrorMessage($uploadedFile->getErrorMessage());

            return false;
        }

        return true;
    }

    public function getData(): UploadedFile
    {
        if ($this->normData === null) {
            $this->normData = new UploadedFile($this->data);
        }

        return $this->normData;
    }
}
