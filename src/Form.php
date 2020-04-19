<?php

namespace Palmtree\Form;

use Palmtree\ArgParser\ArgParser;
use Palmtree\Form\Exception\AlreadySubmittedException;
use Palmtree\Form\Type\AbstractType;
use Palmtree\Html\Element;
use Palmtree\NameConverter\SnakeCaseToCamelCaseNameConverter;

class Form
{
    protected $key;
    /** @var AbstractType[] */
    protected $fields    = [];
    protected $ajax      = false;
    protected $submitted = false;
    protected $method    = 'POST';
    protected $action;
    protected $encType        = '';
    protected $errors         = [];
    protected $fieldWrapper   = 'div.form-group';
    protected $invalidElement = 'div.invalid-feedback.small';
    protected $htmlValidation = true;

    public function __construct($args = [])
    {
        $this->parseArgs($args);
    }

    public function render()
    {
        $formElement = new Element('form.palmtree-form');

        $formElement->attributes->add([
            'method'  => $this->getMethod(),
            'id'      => $this->getKey(),
            'action'  => $this->getAction(),
            'enctype' => $this->getEncType(),
        ]);

        if (!$this->htmlValidation) {
            $formElement->attributes->set('novalidate');
        }

        if ($this->ajax) {
            $formElement->classes[] = 'is-ajax';
        }

        if ($this->submitted) {
            $formElement->classes[] = 'is-submitted';
        }

        $formElement->attributes->setData('invalid_element', htmlentities($this->createInvalidElement()->render()));

        $this->renderFields($formElement);

        if ($this->hasRequiredField()) {
            $info = (new Element('small'))->setInnerText('* required field');

            $formElement->addChild($info);
        }

        return $formElement->render();
    }

    /**
     * @param Element $form
     */
    private function renderFields($form)
    {
        foreach ($this->fields as $field) {
            $fieldWrapper = null;
            $parent       = $form;

            if ($this->fieldWrapper && !$field->isType('hidden')) {
                $fieldWrapper = new Element($this->fieldWrapper);

                if ($field->isRequired()) {
                    $fieldWrapper->classes[] = 'is-required';
                }

                $parent = $fieldWrapper;
            }

            foreach ($field->getElements($parent) as $element) {
                $parent->addChild($element);
            }

            if ($fieldWrapper instanceof Element) {
                $form->addChild($fieldWrapper);
            }
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->fields as $field) {
            if (!$field->isValid()) {
                $this->addError($field->getName(), $field->getErrorMessage());
            }
        }

        return empty($this->errors);
    }

    public function submit($data)
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

    public function handleRequest()
    {
        $requestData = $this->getRequest();
        if (!isset($requestData[$this->getKey()])) {
            return;
        }

        $data = [];

        foreach ($requestData[$this->getKey()] as $key => $value) {
            $data[$key] = $value;
        }

        $data = $this->addFilesToData($data);

        $this->submit($data);
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        switch ($this->method) {
            case 'POST':
                $data = $_POST;
                break;
            case 'GET':
            default:
                $data = $_GET;
                break;
        }

        return $data;
    }

    protected function addFilesToData($data)
    {
        if (!isset($_FILES[$this->getKey()])) {
            return $data;
        }

        foreach ($_FILES[$this->getKey()] as $key => $parts) {
            foreach ($parts as $name => $value) {
                $data[$name][$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @param mixed $key
     *
     * @return Form
     */
    public function setKey($key)
    {
        $this->key = "form_$key";

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param bool $ajax
     *
     * @return Form
     */
    public function setAjax($ajax)
    {
        $this->ajax = $ajax;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return $this->ajax;
    }

    public static function isAjaxRequest()
    {
        $key = 'HTTP_X_REQUESTED_WITH';

        return isset($_SERVER[$key]) && strtolower($_SERVER[$key]) === 'xmlhttprequest';
    }

    /**
     * @return bool
     */
    public function isSubmitted()
    {
        return $this->submitted;
    }

    /**
     * @return Form
     */
    public function setSubmitted()
    {
        $this->submitted = true;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return Form
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * @param string $action
     *
     * @return Form
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @param string $encType
     *
     * @return Form
     */
    public function setEncType($encType)
    {
        $this->encType = $encType;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     *
     * @return Form
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param string $fieldName
     * @param string $errorMessage
     *
     * @return Form
     */
    public function addError($fieldName, $errorMessage)
    {
        $this->errors[$fieldName] = $errorMessage;

        return $this;
    }

    /**
     * @param array $args
     *
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
    public function setFields($fields)
    {
        $this->fields = [];

        foreach ($fields as $field) {
            $this->add($field);
        }
    }

    /**
     * @param string $name
     *
     * @return AbstractType
     */
    public function get($name)
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * @param AbstractType $field
     * @param int|null     $offset
     *
     * @return Form
     */
    public function add(AbstractType $field, $offset = null)
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

    /**
     * @return bool
     */
    public function hasHtmlValidation()
    {
        return $this->htmlValidation;
    }

    /**
     * @param bool $htmlValidation
     *
     * @return Form
     */
    public function setHtmlValidation($htmlValidation)
    {
        $this->htmlValidation = $htmlValidation;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    public function hasRequiredField()
    {
        foreach ($this->getFields() as $field) {
            if ($field->isRequired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    private function parseArgs($args)
    {
        $parser = new ArgParser($args, 'key', new SnakeCaseToCamelCaseNameConverter());

        $parser->parseSetters($this);
    }

    /**
     * @param string $fieldWrapper
     *
     * @return Form
     */
    public function setFieldWrapper($fieldWrapper)
    {
        $this->fieldWrapper = $fieldWrapper;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldWrapper()
    {
        return $this->fieldWrapper;
    }

    /**
     * @return string
     */
    public function getEncType()
    {
        return $this->encType;
    }

    /**
     * @param string $invalidElement
     */
    public function setInvalidElement($invalidElement)
    {
        $this->invalidElement = $invalidElement;
    }

    /**
     * @return string
     */
    public function getInvalidElement()
    {
        return $this->invalidElement;
    }

    public function createInvalidElement()
    {
        $element            = new Element($this->getInvalidElement());
        $element->classes[] = 'palmtree-invalid-feedback';

        return $element;
    }
}
