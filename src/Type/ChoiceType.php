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
    protected $inline  = true;
    protected $choices = [];
    protected $choiceClass;

    public function __construct(array $args = [])
    {
        parent::__construct($args);

        if ($this->expanded) {
            if ($this->multiple) {
                $this->choiceClass = CheckboxType::class;
            } else {
                $this->choiceClass = RadioType::class;
            }
        } else {
            $this->choiceClass = OptionType::class;
        }
    }

    public function getElement(): Element
    {
        $wrapper = new Element('div');
        if ($this->expanded) {
            $parent = $wrapper;
        } else {
            $select = new SelectType([
                'name'        => $this->name,
                'multiple'    => $this->multiple,
                'placeholder' => $this->args['placeholder'],
            ]);

            $select->setForm($this->form);

            $parent = $select->getElement();
        }

        $choiceClass = $this->choiceClass;

        foreach ($this->choices as $value => $label) {
            $args = [
                'data'   => $this->data,
                'parent' => $this,
            ];

            if ($this->multiple) {
                $args['siblings'] = true;
            }

            if (\is_array($label)) {
                $optGroup = new Element('optgroup');

                $optGroup->attributes['label'] = $value;

                foreach ($label as $subValue => $subLabel) {
                    $args['label'] = $subLabel;
                    $args['value'] = $subValue;

                    $choice = new OptionType($args);

                    $choice->setForm($this->form);

                    foreach ($choice->getElements() as $element) {
                        $optGroup->addChild($element);
                    }
                }

                $parent->addChild($optGroup);
            } else {
                $args['label'] = $label;
                $args['value'] = $value;

                $choiceWrapper = null;
                if ($this->expanded) {
                    $args['name'] = $this->name;

                    $choiceWrapper = new Element($this->inline ? 'div.form-check-inline' : 'div.form-check');
                }

                /** @var AbstractType $choice */
                $choice = new $choiceClass($args);

                $choice->setForm($this->form);

                foreach ($choice->getElements() as $child) {
                    // Don't add child feedback as we already display our own.
                    if (!$child->classes->has('palmtree-invalid-feedback')) {
                        if ($child->classes->has('palmtree-form-control') && !$this->isValid()) {
                            $child->classes->add('is-invalid');
                        }

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
        }

        if ($parent !== $wrapper) {
            $wrapper->addChild($parent);
        }

        return $parent;
    }

    public function getElements()
    {
        $elements = parent::getElements();

        foreach ($elements as $element) {
            if ($element->classes->has('palmtree-invalid-feedback')) {
                $element->classes->add('d-block');
                break;
            }
        }

        return $elements;
    }

    public function setChoices(array $choices): self
    {
        $this->choices = $choices;

        return $this;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function setExpanded(bool $expanded): self
    {
        $this->expanded = $expanded;

        return $this;
    }

    /**
     * Returns whether this choice type is expanded i.e not a select box.
     */
    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    public function isInline(): bool
    {
        return $this->inline;
    }

    public function setInline(bool $inline): self
    {
        $this->inline = $inline;

        return $this;
    }
}
