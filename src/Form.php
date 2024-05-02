<?php

declare(strict_types=1);

namespace Palmtree\Form;

use Palmtree\ArgParser\ArgParser;
use Palmtree\Form\DataMapper\ArrayDataMapper;
use Palmtree\Form\DataMapper\DataMapperInterface;
use Palmtree\Form\DataMapper\ObjectDataMapper;
use Palmtree\Form\Exception\AlreadySubmittedException;
use Palmtree\Form\Exception\NotSubmittedException;
use Palmtree\Form\Exception\OutOfBoundsException;
use Palmtree\Form\Type\TypeInterface;
use Palmtree\Html\Element;
use Palmtree\NameConverter\SnakeCaseToCamelCaseNameConverter;

class Form implements \Stringable
{
    protected string $key;
    /** @var array<string, TypeInterface> */
    protected array $fields = [];
    protected bool $ajax = false;
    protected bool $submitted = false;
    protected ?bool $valid = null;
    protected string $method = 'POST';
    protected ?string $action = null;
    protected ?string $encType = null;
    /** @var array<string, string> */
    protected array $errors = [];
    protected string $fieldWrapper = 'div.form-group.mb-3';
    protected string $invalidElementSelector = 'div.invalid-feedback.small';
    protected bool $htmlValidation = true;
    protected FormRenderer $renderer;
    /** @var object|array<string, mixed>|null */
    protected object|array|null $boundData = null;
    protected DataMapperInterface $dataMapper;

    protected const REQUESTED_WITH_HEADER = 'HTTP_X_REQUESTED_WITH';

    /**
     * @param array<string, mixed>|string      $args
     * @param object|array<string, mixed>|null $boundData
     */
    public function __construct(array|string $args = [], object|array|null $boundData = null)
    {
        $this->parseArgs($args);
        $this->renderer = new FormRenderer($this);
        $this->boundData = $boundData;
        $this->dataMapper = $this->createDataMapper();
    }

    public function renderStart(): string
    {
        return $this->renderer->renderStart();
    }

    public function renderEnd(bool $renderRest = true): string
    {
        return $this->renderer->renderEnd($renderRest);
    }

    public function renderRest(): string
    {
        return $this->renderer->renderRest();
    }

    public function render(): string
    {
        return $this->renderer->render();
    }

    public function renderField(string $name): string
    {
        return $this->renderer->renderField($name);
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

    /**
     * @param array<string, mixed> $data
     */
    public function submit(array $data): void
    {
        if ($this->submitted) {
            throw new AlreadySubmittedException(__METHOD__ . ' can only be called once');
        }

        $this->submitted = true;

        foreach ($this->fields as $field) {
            $key = $field->getName();

            $field->clearData();

            if (isset($data[$key]) || \array_key_exists($key, $data)) {
                $field->setData($data[$key]);
            }

            $field->build();
            $field->mapData();
        }

        if ($this->boundData !== null && $this->isValid()) {
            $formData = array_map(fn (TypeInterface $field) => $field->getNormData(), $this->allMapped());

            $this->dataMapper->mapDataFromForm($this->boundData, $formData, $this);
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
            foreach ((array)$parts as $name => $value) {
                $data[$name][$key] = $value;
            }
        }

        $this->submit($data);
    }

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array<string, string> $errors
     */
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
     * @return array<string, TypeInterface>
     */
    public function all(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string, TypeInterface>
     */
    public function allMapped(): array
    {
        return array_filter($this->fields, fn (TypeInterface $field) => $field->isMapped());
    }

    public function has(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    public function get(string $name): TypeInterface
    {
        if (!$this->has($name)) {
            throw new OutOfBoundsException("Field with key '$name' does not exist");
        }

        return $this->fields[$name];
    }

    public function add(TypeInterface ...$fields): self
    {
        foreach ($fields as $field) {
            $field->setForm($this);

            $this->fields[$field->getName()] = $field;
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

    public function getAction(): ?string
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

    public function getEncType(): ?string
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
     * @return array<string, TypeInterface>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param string|array<string, mixed> $args
     */
    private function parseArgs(string|array $args): void
    {
        $parser = new ArgParser($args, 'key', new SnakeCaseToCamelCaseNameConverter());

        $parser->parseSetters($this);
    }

    public function bind(): void
    {
        if ($this->boundData !== null) {
            $this->dataMapper->mapDataToForm($this->boundData, $this);
        }
    }

    public function setDataMapper(DataMapperInterface $dataMapper): self
    {
        $this->dataMapper = $dataMapper;

        return $this;
    }

    private function createDataMapper(): DataMapperInterface
    {
        if (\is_array($this->boundData) || $this->boundData instanceof \ArrayAccess) {
            return new ArrayDataMapper();
        }

        return new ObjectDataMapper();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
