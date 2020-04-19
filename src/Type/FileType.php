<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class FileType extends AbstractType
{
    private $custom     = true;
    private $browseText = 'Browse';

    protected $type = 'file';

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

    public function getLabelElement(): Element
    {
        $element = parent::getLabelElement();

        if ($element && $this->custom) {
            $element->classes[] = 'custom-file-label';
            $element->attributes->setData('browse', $this->getBrowseText());
        }

        return $element;
    }

    public function getElements(Element $wrapper = null)
    {
        $elements = parent::getElements($wrapper);

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
}
