<?php

namespace Palmtree\Form\Type;

use Palmtree\Html\Element;

class ChoiceType extends AbstractType
{
    /** @var bool */
    protected $multiple = false;
    /** @var bool If true, use radio buttons/checkboxes. Otherwise use a select box */
    protected $expanded = false;
    /** @var bool Whether expanded choices should display inline. Has no effect if expanded is false */
    protected $inline = true;
    protected $choices = [];
    protected $choiceClass;

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        if ($this->isExpanded()) {
            if ($this->isMultiple()) {
                $this->choiceClass = CheckboxType::class;
            } else {
                $this->choiceClass = RadioType::class;
            }
        } else {
            $this->choiceClass = OptionType::class;
        }
    }

    public function getElement()
    {
        $wrapper = new Element('div');

        if ($this->isExpanded()) {
            $parent = $wrapper;
        } else {
            $select = new SelectType([
                'name'     => $this->getName(),
                'multiple' => $this->isMultiple(),
            ]);

            $select->setForm($this->getForm());

            $parent = $select->getElement();
        }

        $choiceClass = $this->choiceClass;

        foreach ($this->getChoices() as $value => $text) {
            if (is_int($value)) {
                // Assume it's an array without keys if $value is an int
                $value = $text;
            }

            $args = [
                'label'  => $text,
                'value'  => $value,
                'data'   => $this->getData(),
                'parent' => $this,
            ];

            $choiceWrapper = null;
            if ($this->isExpanded()) {
                $args['name'] = $this->getName();

                $choiceWrapper = new Element($this->isInline() ? 'div.form-check-inline' : 'div.form-check');
            }

            if ($this->isMultiple()) {
                $args['siblings'] = true;
            }

            /** @var AbstractType $choice */
            $choice = new $choiceClass($args);

            $choice->setForm($this->getForm());

            foreach ($choice->getElements() as $child) {
                // Don't add child feedback as we already display our own.
                if (!$child->hasClass('invalid-feedback')) {
                    if ($choiceWrapper) {
                        $choiceWrapper->addChild($child);
                    } else {
                        $parent->addChild($child);
                    }
                }
            }

            if ($choiceWrapper) {
                $parent->addChild($choiceWrapper);
            }
        }

        if ($parent !== $wrapper) {
            $wrapper->addChild($parent);
        }

        return $parent;
    }

    public function setChoices(array $choices)
    {
        $this->choices = $choices;

        return $this;
    }


    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param bool $multiple
     *
     * @return ChoiceType
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param bool $expanded
     *
     * @return ChoiceType
     */
    public function setExpanded($expanded)
    {
        $this->expanded = $expanded;

        return $this;
    }

    /**
     * Returns whether this choice type is expanded i.e not a select box
     *
     * @return bool
     */
    public function isExpanded()
    {
        return $this->expanded;
    }

    /**
     * @return bool
     */
    public function isInline()
    {
        return $this->inline;
    }

    /**
     * @param bool $inline
     *
     * @return ChoiceType
     */
    public function setInline($inline)
    {
        $this->inline = $inline;

        return $this;
    }
}
