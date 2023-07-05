<?php

declare(strict_types=1);

namespace Palmtree\Form;

use Palmtree\Form\Exception\OutOfBoundsException;
use Palmtree\Form\Type\CheckboxType;
use Palmtree\Form\Type\HiddenType;
use Palmtree\Html\Element;

class FormRenderer
{
    private Element $element;
    /** @var array<string, list<Element>> */
    private array $fields = [];
    /** @var list<string> */
    private array $renderedFields = [];
    private bool $built = false;

    public function __construct(private readonly Form $form)
    {
        $this->element = new Element('form.palmtree-form');
    }

    public function renderStart(): string
    {
        $this->buildElement();

        return $this->element->renderStart();
    }

    public function render(): string
    {
        $this->buildElement();

        return $this->element->render();
    }

    public function renderRest(): string
    {
        $html = '';
        foreach ($this->fields as $name => $field) {
            if (!\in_array($name, $this->renderedFields)) {
                $html .= $this->renderField($name);
            }
        }

        return $html;
    }

    public function renderEnd(bool $renderRest = true): string
    {
        $this->buildElement();

        $html = '';

        if ($renderRest) {
            $html .= $this->renderRest();
        }

        $html .= $this->element->renderEnd();

        return $html;
    }

    public function renderField(string $name): string
    {
        $this->buildElement();

        if (!isset($this->fields[$name])) {
            throw new OutOfBoundsException("Field with key '$name' does not exist");
        }

        $html = '';
        foreach ($this->fields[$name] as $field) {
            $html .= $field->render();
        }

        $this->renderedFields[] = $name;

        return $html;
    }

    private function buildElement(): void
    {
        if ($this->built) {
            return;
        }

        $this->element->attributes->add([
            'method' => $this->form->getMethod(),
            'id' => $this->form->getKey(),
        ]);

        if ($this->form->getEncType() !== null) {
            $this->element->attributes->set('enctype', $this->form->getEncType());
        }

        if ($this->form->getAction() !== null) {
            $this->element->attributes->set('action', $this->form->getAction());
        }

        if (!$this->form->hasHtmlValidation()) {
            $this->element->attributes->set('novalidate');
        }

        if ($this->form->isAjax()) {
            $this->element->classes[] = 'is-ajax';
        }

        if ($this->form->isSubmitted()) {
            $this->element->classes[] = 'is-submitted';
        }

        $this->element->attributes->setData('invalid_element', htmlentities($this->form->createInvalidElement()->render()));

        $this->addFieldsToElement();

        $this->built = true;
    }

    private function addFieldsToElement(): void
    {
        foreach ($this->form->getFields() as $field) {
            $fieldWrapper = null;
            $parent = $this->element;

            if ($this->form->getFieldWrapper() && !$field instanceof HiddenType) {
                $fieldWrapper = new Element($this->form->getFieldWrapper());

                if ($field->isRequired()) {
                    $fieldWrapper->classes[] = 'is-required';
                }

                $parent = $fieldWrapper;
            }

            if ($field instanceof CheckboxType) {
                $parent->classes[] = 'form-check';
            }

            foreach ($field->getElements() as $element) {
                $parent->addChild($element);

                if (!$fieldWrapper instanceof Element) {
                    $this->fields[$field->getName()][] = $element;
                }
            }

            if ($fieldWrapper instanceof Element) {
                $this->element->addChild($fieldWrapper);
                $this->fields[$field->getName()] = [$fieldWrapper];
            }
        }
    }
}
