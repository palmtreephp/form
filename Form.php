<?php

namespace Palmtree\Form;

use Palmtree\ArgParser\ArgParser;
use Palmtree\Form\Type\AbstractType;
use Palmtree\Html\Element;

class Form
{
    protected $key;
    /** @var AbstractType[] */
    protected $fields = [];
    protected $ajax = false;
    protected $submitted = false;
    protected $method = 'post';
    protected $action = '';
    protected $encType = '';
    protected $errors = [];
    protected $requestData = [];
    protected $fieldWrapper = 'div.form-group';
    protected $htmlValidation = true;

    public function __construct(array $args = [])
    {
        $this->parseArgs($args);
    }

    public function render()
    {
        $form = new Element('form');

        $form->setAttributes([
            'method' => $this->method,
            'id'     => $this->getKey(),
        ]);

        if (!empty($this->encType)) {
            $form->addAttribute('enctype', $this->encType);
        }

        if (!empty($this->action)) {
            $form->addAttribute('action', $this->action);
        }

        $form->addClass('palmtree-form');

        if ($this->isAjax()) {
            $form->addClass('is-ajax');
        }

        if ($this->isSubmitted()) {
            $form->addClass('is-submitted');
        }

        $this->renderFields($form);

        if ($this->hasRequiredField()) {
            $info = new Element('small');
            $info->setInnerText('* required field');

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

                if (!$field->isValid()) {
                    $fieldWrapper->addClass('has-danger');
                }

                $parent = $fieldWrapper;
            }

            foreach ($field->getElements() as $element) {
                $parent->addChild($element);
            }

            if ($fieldWrapper instanceof Element) {
                $form->addChild($fieldWrapper);
            }
        }
    }

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
        $request = $this->getRequest();
        if (!isset($request[$this->getKey()])) {
            return;
        }

        $this->setSubmitted(true);

        foreach ($request[$this->getKey()] as $key => $value) {
            $this->requestData[$key] = $value;
        }

        foreach ($this->fields as $field) {
            $key = $field->getName();

            if ($field->isGlobal() && array_key_exists($key, $request)) {
                $field->setData($request[$key]);
            } else if (array_key_exists($key, $this->requestData)) {
                $field->setData($this->requestData[$key]);
            }
        }
    }

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
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
               && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * @return bool
     */
    public function isSubmitted()
    {
        return $this->submitted;
    }

    /**
     * @param boolean $submitted
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
     * @return AbstractType
     */
    public function getField($name)
    {
        return (isset($this->fields[$name])) ? $this->fields[$name] : null;
    }

    /**
     * @param AbstractType[] $fields
     */
    public function setFields($fields)
    {
        $this->fields = [];

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    public function addField(AbstractType $field, $offset = null)
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
    public function isHtmlValidation()
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
        $parser = new ArgParser($args);

        $parser->parseSetters($this);
    }
}
