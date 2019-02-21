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
    protected $userInput = true;
    protected $global = false;
    protected $required = true;
    protected $errorMessage = 'Please fill in this field';
    /** @var Form  */
    protected $form;
    /** @var AbstractType|null */
    protected $parent;
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

        if ($this->isRequired()) {
            $this->addConstraint(new NotBlank());
        }
    }

    protected function parseArgs($args)
    {
        $parser = new ArgParser($args, '', new SnakeCaseToCamelCaseNameConverter());

        $parser->parseSetters($this);

        return $parser->resolveOptions(static::$defaultArgs);
    }

    /**
     * @param string $type
     *
     * @return AbstractType
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param mixed $name
     *
     * @return AbstractType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param mixed $label
     *
     * @return AbstractType
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
        if (!$this->getForm()->isSubmitted() || !$this->isRequired()) {
            return true;
        }

        foreach ($this->getConstraints() as $constraint) {
            if (!$constraint->validate($this->getData())) {
                $this->setErrorMessage($constraint->getErrorMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * @return bool|Element
     */
    public function getLabelElement()
    {
        $label = $this->getLabel();

        if (!$label) {
            return false;
        }

        $element = new Element('label');

        $element->addAttribute('for', $this->getIdAttribute())->setInnerText($label);

        if ($this->isRequired() && !$this->getParent()) {
            $abbr = new Element('abbr');

            $abbr->setInnerText('*')->addAttribute('title', 'Required Field');

            $element->addChild($abbr);
        }

        return $element;
    }

    /**
     * @return Element
     */
    public function getElement()
    {
        $args        = $this->args;
        $args['tag'] = $this->getTag();
        $element     = new Element($args);

        $attributes = [
            'type' => $this->getType(),
            'id'   => $this->getIdAttribute(),
            'name' => $this->getNameAttribute(),
        ];

        if ($this->getType() !== 'file') {
            $attributes['value'] = $this->getData();
        }

        if ($attributes['type'] === 'hidden') {
            unset($attributes['placeholder']);
        } else {
            if ($placeholder = $this->getPlaceHolderAttribute()) {
                $attributes['placeholder'] = $placeholder;
            }
        }

        if ($this->isRequired() && $this->getForm()->hasHtmlValidation()) {
            $attributes['required'] = true;
        }

        $element->setAttributes($attributes);

        $element->addDataAttribute('name', $this->getName());

        if ($this->form->hasHtmlValidation() && $this->isRequired()) {
            $element->addAttribute('required');
        }

        $element
            ->addClass('palmtree-form-control')
            ->addClass('form-control');

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

        if (!$element->getAttribute('id')) {
            $element->addAttribute('id', $this->getIdAttribute());
        }

        if (!$this->isValid()) {
            $element->addClass('is-invalid');
        }

        $elements[] = $element;

        if (!$this->isValid()) {
            $error = new Element('div.invalid-feedback.small');
            $error->setInnerText($this->getErrorMessage());
            $elements[] = $error;
        }

        return $elements;
    }

    /**
     * @return string
     */
    public function getHumanName()
    {
        return $this->nameConverter->normalize($this->getName());
    }

    public function getNameAttribute()
    {
        $formId = $this->form->getKey();
        $name   = $this->getName();

        if ($this->isGlobal()) {
            return $name;
        }

        $format = '%s[%s]';

        if ($this->getParent()) {
            $format .= '[]';
        }

        return sprintf($format, $formId, $name);
    }

    protected function getIdAttribute()
    {
        $formId = $this->form->getKey();
        $name   = $this->getName();

        if ($this->isGlobal()) {
            return $name;
        }

        return "$formId-$name";
    }

    /**
     * @return string
     */
    public function getPlaceHolderAttribute()
    {
        $placeholder = '';

        if ($this->args['placeholder'] === true) {
            $placeholder = 'Enter your ' . strtolower($this->getHumanName());
        } elseif (is_string($this->args['placeholder'])) {
            $placeholder = $this->args['placeholder'];
        }

        return $placeholder;
    }

    /**
     * @return string|array|mixed
     */
    public function getData($default = '')
    {
        /*if ( $this->value === null ) {
            return $default;
        }*/

        return $this->data;
    }

    /**
     * @param string|array|mixed $data
     *
     * @return AbstractType
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
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
     * @return AbstractType
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return AbstractType
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
        return (string)$type === (string)$this->getType();
    }

    /**
     * @param boolean $required
     *
     * @return AbstractType
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return boolean
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
     * @return AbstractType
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return AbstractType|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param AbstractType $parent
     *
     * @return AbstractType
     */
    public function setParent(AbstractType $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param boolean $global
     *
     * @return AbstractType
     */
    public function setGlobal($global)
    {
        $this->global = $global;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isGlobal()
    {
        return $this->global;
    }

    /**
     * @return boolean
     */
    public function isUserInput()
    {
        return $this->userInput;
    }

    /**
     * @param boolean $userInput
     *
     * @return AbstractType
     */
    public function setUserInput($userInput)
    {
        $this->userInput = $userInput;

        return $this;
    }

    public function filter(array $args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                if ($this->$key !== $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param ConstraintInterface $constraint
     *
     * @return AbstractType
     */
    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @param ConstraintInterface[] $constraints
     *
     * @return AbstractType
     */
    public function setConstraints($constraints)
    {
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
