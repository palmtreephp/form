<?php

namespace Palmtree\Form;

use Palmtree\ArgParser\ArgParser;
use Palmtree\Form\Exception\AlreadySubmittedException;
use Palmtree\Form\Exception\NotSubmittedException;
use Palmtree\Form\Type\AbstractType;
use Palmtree\Form\Type\CheckboxType;
use Palmtree\Form\Type\HiddenType;
use Palmtree\Html\Element;
use Palmtree\NameConverter\SnakeCaseToCamelCaseNameConverter;

class Form
{
    /** @var string */
    protected $key;
    /** @var AbstractType[] */
    protected $fields = [];
    /** @var bool */
    protected $ajax = false;
    /** @var bool */
    protected $submitted = false;
    /** @var bool|null */
    protected $valid;
    /** @var string */
    protected $method = 'POST';
    /** @var string */
    protected $action;
    /** @var string */
    protected $encType = '';
    /** @var array */
    protected $errors = [];
    /** @var string */
    protected $fieldWrapper = 'div.form-group';
    /** @var string */
    protected $invalidElementSelector = 'div.invalid-feedback.small';
    /** @var bool */
    protected $htmlValidation = true;
    /** @var string */
    private const REQUESTED_WITH_HEADER = 'HTTP_X_REQUESTED_WITH';

    public function __construct(array $args = [])
    {
        $this->parseArgs($args);
    }

    public function render(): string
    {
        $element = new Element('form.palmtree-form');

        $element->attributes->add([
            'method'  => $this->method,
            'id'      => $this->key,
            'action'  => $this->action,
            'enctype' => $this->encType,
        ]);

        if (!$this->htmlValidation) {
            $element->attributes->set('novalidate');
        }

        if ($this->ajax) {
            $element->classes[] = 'is-ajax';
        }

        if ($this->submitted) {
            $element->classes[] = 'is-submitted';
        }

        $element->attributes->setData('invalid_element', htmlentities($this->createInvalidElement()->render()));

        $this->renderFields($element);

        if ($this->hasRequiredField()) {
            $info = (new Element('small'))->setInnerText('* required field');

            $element->addChild($info);
        }

        return $element->render();
    }

    private function renderFields(Element $form): void
    {
        foreach ($this->fields as $field) {
            $fieldWrapper = null;
            $parent       = $form;

            if ($this->fieldWrapper && !$field instanceof HiddenType) {
                $fieldWrapper = new Element($this->fieldWrapper);

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
            }

            if ($fieldWrapper instanceof Element) {
                $form->addChild($fieldWrapper);
            }
        }
    }

    public function isValid(): bool
    {
        if (!$this->submitted) {
            throw new NotSubmittedException('Form must be submitted before calling ' . __METHOD__);
        }

        if ($this->valid === null) {
            $this->valid = true;
            foreach ($this->fields as $field) {
                if (!$field->isValid()) {
                    $this->valid = false;
                    if ($errorMessage = $field->getErrorMessage()) {
                        $this->addError($field->getName(), $errorMessage);
                    }
                }
            }
        }

        return $this->valid;
    }

    public function submit(array $data): void
    {
        if ($this->submitted) {
            throw new AlreadySubmittedException(__METHOD__ . ' can only be called once');
        }

        $this->submitted = true;

        foreach ($this->fields as $field) {
            $key = $field->getName();

            if (isset($data[$key]) || \array_key_exists($key, $data)) {
                $field->setData($data[$key]);
            }

            $field->build();
            $field->mapData();
        }
    }

    public function handleRequest(): void
    {
        $requestData = $this->getRequestData();

        if (!isset($requestData[$this->key])) {
            return;
        }

        $data = [];

        foreach ($requestData[$this->key] ?? [] as $key => $value) {
            $data[$key] = $value;
        }

        foreach ($_FILES[$this->key] ?? [] as $key => $parts) {
            foreach ($parts as $name => $value) {
                $data[$name][$key] = $value;
            }
        }

        $this->submit($data);
    }

    public function getRequestData(): array
    {
        return strtoupper($this->method) === 'POST' ? $_POST : $_GET;
    }

    public function setKey(string $key): self
    {
        $this->key = "form_$key";

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setAjax(bool $ajax): self
    {
        $this->ajax = $ajax;

        return $this;
    }

    public function isAjax(): bool
    {
        return $this->ajax;
    }

    public static function isAjaxRequest(): bool
    {
        return strtolower($_SERVER[self::REQUESTED_WITH_HEADER] ?? '') === 'xmlhttprequest';
    }

    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);

        return $this;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function setEncType(string $encType): self
    {
        $this->encType = $encType;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function addError(string $fieldName, string $errorMessage): self
    {
        $this->errors[$fieldName] = $errorMessage;

        return $this;
    }

    /**
     * @return AbstractType[]
     */
    public function getFields(array $args = [])
    {
        if (empty($args)) {
            return $this->fields;
        }

        return array_filter($this->fields, static function (AbstractType $field) use ($args) {
            return $field->filter($args);
        });
    }

    /**
     * @param AbstractType[] $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = [];

        foreach ($fields as $field) {
            $this->add($field);
        }
    }

    public function get(string $name): ?AbstractType
    {
        return $this->fields[$name] ?? null;
    }

    public function add(AbstractType $field, ?int $offset = null): self
    {
        $field->setForm($this);

        if ($offset === null) {
            $this->fields[$field->getName()] = $field;
        } else {
            if ($offset < 0) {
                $totalFields = \count($this->fields);
                $offset      = $totalFields - $offset + 1;
            }

            // Add the field at the specified offset
            $this->fields = array_merge(
                \array_slice($this->fields, 0, $offset, true),
                [$field->getName() => $field],
                \array_slice($this->fields, $offset, null, true)
            );
        }

        return $this;
    }

    public function hasHtmlValidation(): bool
    {
        return $this->htmlValidation;
    }

    public function setHtmlValidation(bool $htmlValidation): self
    {
        $this->htmlValidation = $htmlValidation;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function hasRequiredField(): bool
    {
        foreach ($this->fields as $field) {
            if ($field->isRequired()) {
                return true;
            }
        }

        return false;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setFieldWrapper(string $fieldWrapper): self
    {
        $this->fieldWrapper = $fieldWrapper;

        return $this;
    }

    public function getFieldWrapper(): string
    {
        return $this->fieldWrapper;
    }

    public function getEncType(): string
    {
        return $this->encType;
    }

    public function setInvalidElementSelector(string $invalidElementSelector): void
    {
        $this->invalidElementSelector = $invalidElementSelector;
    }

    public function getInvalidElementSelector(): string
    {
        return $this->invalidElementSelector;
    }

    public function createInvalidElement(): Element
    {
        $element = new Element($this->invalidElementSelector);

        $element->classes[] = 'palmtree-invalid-feedback';

        return $element;
    }

    /**
     * @param string|array $args
     */
    private function parseArgs($args): void
    {
        $parser = new ArgParser($args, 'key', new SnakeCaseToCamelCaseNameConverter());

        $parser->parseSetters($this);
    }
}
