<?php

namespace Palmtree\Form\Type;

use Palmtree\ArgParser\ArgParser;
use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\Constraint\NotBlank;
use Palmtree\Form\Form;
use Palmtree\Html\Element;
use Palmtree\NameConverter\SnakeCaseToCamelCaseNameConverter;
use Palmtree\NameConverter\SnakeCaseToHumanNameConverter;

abstract class AbstractType
{
    protected $tag = 'input';
    protected $type;
    protected $name;
    protected $data;
    protected $label;
    protected $userInput    = true;
    protected $global       = false;
    protected $required     = true;
    protected $errorMessage = 'Please fill in this field';
    /** @var Form */
    protected $form;
    /** @var AbstractType|null */
    protected $parent;
    /** @var AbstractType[] */
    protected $children = [];
    protected $position = 0;
    /** @var array */
    protected $args = [];
    /** @var ConstraintInterface[] */
    protected $constraints = [];
    /** @var SnakeCaseToHumanNameConverter */
    protected $nameConverter;

    public static $defaultArgs = [
        'placeholder' => true,
        'classes'     => [],
    ];

    public function __construct(array $args = [])
    {
        $this->nameConverter = new SnakeCaseToHumanNameConverter();

        $this->args = $this->parseArgs($args);

        if ($this->required) {
            $this->addConstraint(new NotBlank($this->errorMessage));
        }
    }

    protected function parseArgs(array $args): array
    {
        $parser = new ArgParser($args, '', new SnakeCaseToCamelCaseNameConverter());

        $parser->parseSetters($this);

        return $parser->resolveOptions(static::$defaultArgs);
    }

    public function build(): void
    {
        foreach ($this->children as $child) {
            $child->build();
        }
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isValid(): bool
    {
        if (!$this->form->isSubmitted()) {
            return true;
        }

        foreach ($this->getConstraints() as $constraint) {
            if (!$constraint->validate($this->data)) {
                $this->setErrorMessage($constraint->getErrorMessage());

                return false;
            }
        }

        foreach ($this->children as $child) {
            if (!$child->isValid()) {
                return false;
            }
        }

        return true;
    }

    public function getLabelElement(): ?Element
    {
        if (!$this->label) {
            return null;
        }

        $element = Element::create('label[for="' . $this->getIdAttribute() . '"]')->setInnerText($this->label);

        if ($this->required && !$this->parent) {
            $element->addChild(Element::create('abbr[title="Required Field"]')->setInnerText('*'));
        }

        return $element;
    }

    public function getElement(): Element
    {
        $element = new Element($this->tag);
        $element->classes->add(...$this->args['classes'] ?? []);

        $element->attributes->add([
            'type' => $this->type,
            'id'   => $this->getIdAttribute(),
            'name' => $this->getNameAttribute(),
        ]);

        if ($placeholder = $this->getPlaceHolderAttribute()) {
            $element->attributes['placeholder'] = $placeholder;
        }

        if ($this->required && $this->form->hasHtmlValidation()) {
            $element->attributes['required'] = true;
        }

        if (!\is_array($this->data)) {
            $element->attributes['value'] = $this->data;
        }

        if ($this->name) {
            $element->attributes->setData('name', $this->name);
        }

        if ($this->required && $this->form->hasHtmlValidation()) {
            $element->attributes->set('required');
        }

        $element->classes->add('palmtree-form-control', 'form-control');

        return $element;
    }

    /**
     * @return Element[]
     */
    public function getElements()
    {
        $elements = [];

        $label = $this->getLabelElement();

        if ($label instanceof Element) {
            $elements[] = $label;
        }

        $element = $this->getElement();

        if (!$element->attributes['id']) {
            $element->attributes['id'] = $this->getIdAttribute();
        }

        $isValid = $this->isValid();

        if (!$isValid) {
            $element->classes[] = 'is-invalid';
        }

        $elements[] = $element;

        if (!$isValid) {
            $elements[] = $this->form->createInvalidElement()->setInnerText($this->errorMessage);
        }

        return $elements;
    }

    public function getHumanName(): string
    {
        return $this->nameConverter->normalize($this->name);
    }

    public function getNameAttribute(): string
    {
        if ($this->global) {
            return $this->name;
        }

        $formId = $this->form->getKey();

        if ($this->parent) {
            return sprintf(
                '%s[%s][%s][%s]',
                $formId,
                $this->parent->getName(),
                $this->parent->getPosition(),
                $this->name
            );
        }

        return sprintf('%s[%s]', $formId, $this->name);
    }

    protected function getIdAttribute(): string
    {
        if ($this->global) {
            return $this->name;
        }

        return $this->form->getKey() . "-$this->name";
    }

    public function getPlaceHolderAttribute(): string
    {
        $placeholder = '';

        if ($this->args['placeholder'] === true) {
            $placeholder = 'Enter your ' . strtolower($this->getHumanName());
        } elseif (\is_string($this->args['placeholder'])) {
            $placeholder = $this->args['placeholder'];
        }

        return $placeholder;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function mapData(): void
    {
        foreach ($this->data ?? [] as $key => $value) {
            if ($child = $this->getChild($key)) {
                $child->setData($value);
                $child->mapData();
            }
        }
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isType(string $type): bool
    {
        return $type === $this->type;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function setForm(Form $form): self
    {
        $this->form = $form;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(self $parent): self
    {
        $this->parent = $parent;
        $this->form   = $parent->getForm();

        return $this;
    }

    public function addChild(self $child): self
    {
        if ($child->getParent() !== $this) {
            $child->setParent($this);
        }

        $this->children[$child->getName()] = $child;

        return $this;
    }

    public function add(string $name, string $fqcn, array $options = []): self
    {
        /** @var self $type */
        $type = new $fqcn($options);
        $type->setName($name);

        $this->addChild($type);

        return $this;
    }

    /**
     * @return AbstractType[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getChild(string $name): ?self
    {
        return $this->children[$name] ?? null;
    }

    public function setPosition($position): void
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setGlobal(bool $global): self
    {
        $this->global = $global;

        return $this;
    }

    public function isGlobal(): bool
    {
        return $this->global;
    }

    public function isUserInput(): bool
    {
        return $this->userInput;
    }

    public function setUserInput(bool $userInput): self
    {
        $this->userInput = $userInput;

        return $this;
    }

    public function filter(array $args = []): bool
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && $this->$key !== $value) {
                return false;
            }
        }

        return true;
    }

    public function addConstraint(ConstraintInterface $constraint): self
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @param ConstraintInterface[] $constraints
     */
    public function setConstraints(array $constraints): self
    {
        $this->constraints = [];

        foreach ($constraints as $constraint) {
            $this->addConstraint($constraint);
        }

        return $this;
    }

    /**
     * @return ConstraintInterface[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
