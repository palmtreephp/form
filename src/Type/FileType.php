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

        if ($this->isCustom() && !$this->getLabel()) {
            $this->setLabel('Choose file...');
        }
    }

    public function getElement()
    {
        $element = parent::getElement();

        $element->removeAttribute('value');

        if ($this->isCustom()) {
            $element->addClass('custom-file-input');
        }

        return $element;
    }

    public function getLabelElement()
    {
        $element = parent::getLabelElement();

        if ($element && $this->isCustom()) {
            $element->addClass('custom-file-label');
            $element->addDataAttribute('browse', $this->getBrowseText());
        }

        return $element;
    }

    public function getElements(Element $wrapper = null)
    {
        $elements = parent::getElements($wrapper);

        if (!$this->isCustom()) {
            return $elements;
        }

        $customFileWrapper = new Element('div.custom-file');

        // Add input first because label needs to be below the input element in the DOM
        $customFileWrapper->addChild($elements[1]);
        unset($elements[1]);

        foreach ($elements as $element) {
            $customFileWrapper->addChild($element);
        }

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
