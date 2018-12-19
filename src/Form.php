<?php

namespace Palmtree\Form;

use Palmtree\ArgParser\ArgParser;
use Palmtree\Form\Type\AbstractType;
use Palmtree\Html\Element;
use Palmtree\NameConverter\SnakeCaseToCamelCaseNameConverter;

class Form
{
    protected $key;
    /** @var AbstractType[] */
    protected $fields = [];
    protected $ajax = false;
    protected $submitted = false;
    protected $method = 'POST';
    protected $action;
    protected $encType = '';
    protected $errors = [];
    protected $requestData = [];
    protected $fieldWrapper = 'div.form-group';
    protected $htmlValidation = true;

    public function __construct($args = [])
    {
        $this->parseArgs($args);
    }

    public function render()
    {
        $form = new Element('form');

        $form
            ->addClass('palmtree-form')
            ->setAttributes([
                'method'  => $this->getMethod(),
                'id'      => $this->getKey(),
                'action'  => $this->getAction(),
                'enctype' => $this->getEncType(),
            ]);

        if (!$this->hasHtmlValidation()) {
            $form->addAttribute('novalidate', true);
        }

        if ($this->isAjax()) {
            $form->addClass('is-ajax');
        }

        if ($this->isSubmitted()) {
            $form->addClass('is-submitted');
        }

        $this->renderFields($form);

        if ($this->hasRequiredField()) {
            $info = (new Element('small'))->setInnerText('* required field');

            $form->addChild($info);
        }

        return $form->render();
    }

    /**
     * @param Element $form
     */
    private function renderFields($form)
    {
        foreach ($this->getFields() as $field) {
            $fieldWrapper = null;
            $parent       = $form;

            if ($this->fieldWrapper && !$field->isType('hidden')) {
                $fieldWrapper = new Element($this->fieldWrapper);

                if ($field->isRequired()) {
                    $fieldWrapper->addClass('is-required');
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
        foreach ($this->getFields() as $field) {
            if (!$field->isValid()) {
                $this->addError($field->getName(), $field->getErrorMessage());
            }
        }

        return empty($this->errors);
    }

    public function handleRequest()
    {
        $requestData = $this->getRequest();
        if (!isset($requestData[$this->getKey()])) {
            return;
        }

        $this->setSubmitted(true);

        foreach ($requestData[$this->getKey()] as $key => $value) {
            $this->requestData[$key] = $value;
        }

        $this->addFilesToRequestData();

        foreach ($this->getFields() as $field) {
            $key = $field->getName();

            if ($field->isGlobal() && array_key_exists($key, $requestData)) {
                $field->setData($requestData[$key]);
            } elseif (array_key_exists($key, $this->requestData)) {
                $field->setData($this->requestData[$key]);
            }
        }
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        switch ($this->getMethod()) {
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

    /**
     *
     */
    protected function addFilesToRequestData()
    {
        if (!isset($_FILES[$this->getKey()])) {
            return;
        }

        foreach ($_FILES[$this->getKey()] as $key => $parts) {
            foreach ($parts as $name => $value) {
                $this->requestData[$name][$key] = $value;
            }
        }
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
     * @param boolean $ajax
     *
     * @return Form
     */
    public function setAjax($ajax)
    {
        $this->ajax = $ajax;

        return $this;
    }

    /**
     * @return boolean
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
     * @param bool $submitted
     *
     * @return Form
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

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

        $fields = array_filter($this->fields, function ($field) use ($args) {
            /** @var AbstractType $field */
            return $field->filter($args);
        });

        return $fields;
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
     * @deprecated Use get() instead
     *
     * @param string $name
     *
     * @return AbstractType
     */
    public function getField($name)
    {
        trigger_error(
            'The ' . __METHOD__ . ' method is deprecated since v0.12 and will be removed in 1.0.',
            E_USER_DEPRECATED
        );

        return $this->get($name);
    }

    /**
     * @param string $name
     *
     * @return AbstractType
     */
    public function get($name)
    {
        return (isset($this->fields[$name])) ? $this->fields[$name] : null;
    }

    /**
     * @deprecated Use add() instead
     * @param AbstractType $field
     * @param int|null     $offset
     *
     * @return Form
     */
    public function addField(AbstractType $field, $offset = null)
    {
        trigger_error(
            'The ' . __METHOD__ . ' method is deprecated since v0.12 and will be removed in 1.0.',
            E_USER_DEPRECATED
        );

        return $this->add($field, $offset);
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
                $totalFields = count($this->fields);
                $offset      = $totalFields - $offset + 1;
            }

            // Add the field at the specified offset
            $this->fields = array_merge(
                array_slice($this->fields, 0, $offset, true),
                [$field->getName() => $field],
                array_slice($this->fields, $offset, null, true)
            );
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasHtmlValidation()
    {
        return $this->htmlValidation;
    }

    /**
     * @param boolean $htmlValidation
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
}
