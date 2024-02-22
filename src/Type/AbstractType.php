<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\ArgParser\ArgParser;
use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\Constraint\NotBlank;
use Palmtree\Form\Exception\InvalidTypeException;
use Palmtree\Form\Exception\OutOfBoundsException;
use Palmtree\Form\Form;
use Palmtree\Form\TypeLocator;
use Palmtree\Html\Element;
use Palmtree\NameConverter\SnakeCaseToCamelCaseNameConverter;
use Palmtree\NameConverter\SnakeCaseToHumanNameConverter;

abstract class AbstractType implements TypeInterface
{
    /** @var string */
    protected $tag = 'input';
    /** @var string */
    protected $type;
    /** @var string */
    protected $name;
    /** @var mixed */
    protected $data;
    /** @var string|null */
    protected $label;
    /** @var bool */
    protected $userInput = true;
    /** @var bool */
    protected $required = true;
    /** @var string|null */
    protected $errorMessage = 'Please fill in this field';
    /** @var Form */
    protected $form;
    /** @var TypeInterface|null */
    protected $parent;
    /** @var array<string|int, TypeInterface> */
    protected $children = [];
    /** @var int */
    protected $position = 0;
    /** @var array */
    protected $args = [];
    /** @var array<int, ConstraintInterface> */
    protected $constraints = [];
    /** @var SnakeCaseToHumanNameConverter */
    protected $nameConverter;
    /** @var TypeLocator */
    protected $typeLocator;
    /** @var bool */
    protected $mapped = true;
    /** @var string|null */
    protected $help = null;
    /** @var array */
    public static $defaultArgs = [
        'placeholder' => true,
        'classes' => [],
    ];

    public function __construct(array $args = [])
    {
        $this->nameConverter = new SnakeCaseToHumanNameConverter();
        $this->typeLocator = new TypeLocator();

        $this->args = $this->parseArgs($args);

        if ($this->required && $this->errorMessage) {
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

    public function setType(string $type): TypeInterface
    {
        $this->type = $type;

        return $this;
    }

    public function setName(string $name): TypeInterface
    {
        $this->name = $name;

        return $this;
    }

    public function setLabel(?string $label): TypeInterface
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

        foreach ($this->constraints as $constraint) {
            // We use $this->getData() instead of $this->data here so that the
            // data can be normalized by its type class before validation
            if (!$constraint->validate($this->getData())) {
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

        $element = Element::create('label[for="' . $this->getIdAttribute() . '"].form-label')->setInnerText($this->label);

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
            'id' => $this->getIdAttribute(),
            'name' => $this->getNameAttribute(),
        ]);

        $element->attributes->add($this->args['attr'] ?? []);

        if ($placeholder = $this->getPlaceHolderAttribute()) {
            $element->attributes['placeholder'] = $placeholder;
        }

        if (!\is_array($this->data) && $this->data !== null) {
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

    public function getElements(): array
    {
        $elements = [];

        $label = $this->getLabelElement();

        if ($label instanceof Element) {
            $elements[] = $label;
        }

        $element = $this->getElement();

        if (!isset($element->attributes['id'])) {
            $element->attributes['id'] = $this->getIdAttribute();
        }

        $isValid = $this->isValid();

        if (!$isValid) {
            $element->classes[] = 'is-invalid';
        }

        $elements[] = $element;

        if ($help = $this->getHelp()) {
            $elements[] = Element::create('div.help-text.form-text')->setInnerText($help);
        }

        if (!$isValid && $this->errorMessage) {
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
        $formId = $this->form->getKey();

        if ($this->parent) {
            $format = '%s[%s][%s]';
            $args = [$formId, $this->parent->getName(), $this->getPosition()];

            if (!$this->parent instanceof CollectionType) {
                $format .= '[%s]';
                $args[2] = $this->parent->getPosition();
                $args[] = $this->name;
            }

            return vsprintf($format, $args);
        }

        return $this->form->getKey() . "[$this->name]";
    }

    protected function getIdAttribute(): string
    {
        $value = $this->form->getKey();

        if ($this->name) {
            $value .= "-$this->name";
        }

        if ($this->parent) {
            $value .= '-' . $this->parent->getPosition();
        }

        return $value;
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

    public function getNormData()
    {
        return $this->data;
    }

    public function clearData(): void
    {
        $this->data = null;
    }

    public function setData($data): TypeInterface
    {
        $this->data = $data;

        return $this;
    }

    public function mapData(): void
    {
        if (\is_array($this->data)) {
            foreach ($this->data as $key => $value) {
                $key = (string)$key;
                if ($this->has($key)) {
                    $child = $this->get($key);
                    $child->setData($value);
                    $child->mapData();
                }
            }
        }
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(string $errorMessage): TypeInterface
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function setTag(string $tag): TypeInterface
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

    public function setRequired(bool $required): TypeInterface
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

    public function setForm(Form $form): TypeInterface
    {
        $this->form = $form;

        return $this;
    }

    public function getParent(): ?TypeInterface
    {
        return $this->parent;
    }

    public function setParent(TypeInterface $parent): TypeInterface
    {
        $this->parent = $parent;
        $this->form = $parent->getForm();

        return $this;
    }

    public function addChild(TypeInterface $child): TypeInterface
    {
        if ($child->getParent() !== $this) {
            $child->setParent($this);
        }

        $this->children[$child->getName()] = $child;

        return $this;
    }

    public function add(string $name, string $class, array $options = []): TypeInterface
    {
        if (!$class = $this->typeLocator->getTypeClass($class)) {
            throw new InvalidTypeException('Type could not be found');
        }

        /** @var TypeInterface $type */
        $type = new $class($options);

        $type->setName($name);

        if ($type->getLabel() === null) {
            $type->setLabel($type->getHumanName());
        }

        $this->addChild($type);

        return $this;
    }

    public function all(): array
    {
        return $this->children;
    }

    public function get(string $name): TypeInterface
    {
        if (!$this->has($name)) {
            throw new OutOfBoundsException("Key '$name' does not exist as a child of this field");
        }

        return $this->children[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->children[$name]);
    }

    public function setPosition(int $position): TypeInterface
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function isUserInput(): bool
    {
        return $this->userInput;
    }

    public function setUserInput(bool $userInput): TypeInterface
    {
        $this->userInput = $userInput;

        return $this;
    }

    public function addConstraint(ConstraintInterface ...$constraints): TypeInterface
    {
        foreach ($constraints as $constraint) {
            $this->constraints[] = $constraint;
        }

        return $this;
    }

    /** @return array<int, ConstraintInterface> */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function clearConstraints(): TypeInterface
    {
        $this->constraints = [];

        return $this;
    }

    public function isMapped(): bool
    {
        return $this->mapped;
    }

    public function setHelp(?string $help): void
    {
        $this->help = $help;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }
}
