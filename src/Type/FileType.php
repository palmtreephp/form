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

    public function getElement()
    {
        $element = parent::getElement();

        unset($element->attributes['value']);

        if ($this->custom) {
            $element->classes[] = 'custom-file-input';
        }

        return $element;
    }

    public function getLabelElement()
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

    /**
     * @param bool $custom
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return $this->custom;
    }

    /**
     * @param string $browseText
     */
    public function setBrowseText($browseText)
    {
        $this->browseText = $browseText;
    }

    /**
     * @return string
     */
    public function getBrowseText()
    {
        return $this->browseText;
    }
}
