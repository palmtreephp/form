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

    protected function parseArgs($args)
    {
        $parser = new ArgParser($args, '', new SnakeCaseToCamelCaseNameConverter());

        $parser->parseSetters($this);

        return $parser->resolveOptions(static::$defaultArgs);
    }

    public function build()
    {
        foreach ($this->getChildren() as $child) {
            $child->build();
        }
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param mixed $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param mixed $label
     *
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isValid()
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

    /**
     * @return Element|null
     */
    public function getLabelElement()
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

    /**
     * @return Element
     */
    public function getElement()
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
     * @param Element|null $wrapper
     *
     * @return Element[]
     */
    public function getElements(Element $wrapper = null)
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

    /**
     * @return string
     */
    public function getHumanName()
    {
        return $this->nameConverter->normalize($this->name);
    }

    public function getNameAttribute()
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

    protected function getIdAttribute()
    {
        if ($this->global) {
            return $this->name;
        }

        return $this->form->getKey() . "-$this->name";
    }

    /**
     * @return string
     */
    public function getPlaceHolderAttribute()
    {
        $placeholder = '';

        if ($this->args['placeholder'] === true) {
            $placeholder = 'Enter your ' . strtolower($this->getHumanName());
        } elseif (\is_string($this->args['placeholder'])) {
            $placeholder = $this->args['placeholder'];
        }

        return $placeholder;
    }

    /**
     * @return string|array|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string|array|mixed $data
     *
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function mapData()
    {
        if (\is_array($this->data)) {
            foreach ($this->data as $key => $value) {
                if ($child = $this->getChild($key)) {
                    $child->setData($value);
                    $child->mapData();
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param mixed $errorMessage
     *
     * @return self
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return self
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function isType($type)
    {
        return $type === $this->type;
    }

    /**
     * @param bool $required
     *
     * @return self
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     *
     * @return self
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return self|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param self $parent
     *
     * @return self
     */
    public function setParent(self $parent)
    {
        $this->parent = $parent;
        $this->form   = $parent->getForm();

        return $this;
    }

    /**
     * @param self $child
     *
     * @return self
     */
    public function addChild(self $child)
    {
        if ($child->getParent() !== $this) {
            $child->setParent($this);
        }

        $this->children[$child->getName()] = $child;

        return $this;
    }

    public function add($name, $fqcn, array $options = [])
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
    public function getChildren()
    {
        return $this->children;
    }

    public function getChild($name)
    {
        return isset($this->children[$name]) ? $this->children[$name] : null;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param bool $global
     *
     * @return self
     */
    public function setGlobal($global)
    {
        $this->global = $global;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGlobal()
    {
        return $this->global;
    }

    /**
     * @return bool
     */
    public function isUserInput()
    {
        return $this->userInput;
    }

    /**
     * @param bool $userInput
     *
     * @return self
     */
    public function setUserInput($userInput)
    {
        $this->userInput = $userInput;

        return $this;
    }

    public function filter(array $args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && $this->$key !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ConstraintInterface $constraint
     *
     * @return self
     */
    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @param ConstraintInterface[] $constraints
     *
     * @return self
     */
    public function setConstraints($constraints)
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
    public function getConstraints()
    {
        return $this->constraints;
    }
}
