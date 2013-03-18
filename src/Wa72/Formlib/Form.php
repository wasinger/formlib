<?php
namespace Wa72\Formlib;

use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Form represents a HTML form
 *
 */
class Form
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \DOMDocument
     */
    protected $domdocument;

    /**
     * @var Field[]
     */
    protected $fields;

    /**
     * @var bool
     */
    private $bound = false;

    /**
     * @var bool
     */
    private $has_errors = false;

    /**
     * Contains global error messages that belong to the whole form, not to individual fields
     * @var string[]
     */
    protected $error_messages = array();

    /**
     * @param string $name
     * @param \DOMDocument|null $domdocument
     */
    public function __construct($name, $domdocument = null)
    {
        $this->name = $name;
        if ($domdocument instanceof \DOMDocument) {
            $this->domdocument = $domdocument;
        } else {
            $this->domdocument = new \DOMDocument('1.0', 'UTF-8');
        }
        $this->domdocument->formatOutput = true;
    }

    /**
     * Add a field to the form
     *
     * @param \Wa72\Formlib\Field $field
     * @return \Wa72\Formlib\Form self-reference for chaining
     */
    public function add(Field $field)
    {
        $this->fields[$field->getName()] = $field;
        $field->form = $this;
        return $this;
    }

    /**
     * @param string $fieldname
     * @return Field
     * @throws  \InvalidArgumentException
     */
    public function get($fieldname)
    {
        if (!isset($this->fields[$fieldname])) throw new \InvalidArgumentException('Field ' . $fieldname . ' does not exist');
        return $this->fields[$fieldname];
    }

    /**
     * Get the name of the form
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DOMDocument
     */
    public function getDomdocument()
    {
        return $this->domdocument;
    }

    public function bind($array)
    {
        $vars = $array[$this->name];
        foreach ($this->fields as $field) {
            if (isset($vars[$field->getName()])) {
                $field->setData($vars[$field->getName()]);
            }
        }
        $this->bound = true;
    }

    /**
     * @return bool
     * @throws \LogicException
     */
    public function isValid()
    {
        if (!$this->bound) {
            throw new \LogicException('Form cannot be validated before bind() is called');
        }
        $this->has_errors = false;
        foreach ($this->fields as $field) {
            $field->validate();
            if ($field->getValidationStatus() == ValidatorInterface::STATUS_INVALID) $this->has_errors = true;
        }
        return !$this->has_errors;
    }

    /**
     * Get form data as an array
     *
     * @param bool $for_humans if set, return labels instead of field names and choice values
     * @return array
     */
    public function getData($for_humans = false)
    {
        $data = array();
        foreach ($this->fields as $name => $field) {
            if (!$field->getIncludeInData()) continue;
            if ($for_humans) {
                $label = $field->getLabel();
                if (!$label) $label = $name;
                $data[$label] = $field->getData($for_humans);
            } else {
                $data[$name] = $field->getData();
            }
        }
        return $data;
    }

    /**
     * Get global error messages
     *
     * @return string[]
     */
    public function getErrorMessages()
    {
        $te = array();
        if ($this->has_errors) {
            $te[] = ErrorMessages::$form_has_errors;
        }
        return array_merge($te, $this->error_messages);
    }

    /**
     * Adds a global error message
     *
     * @param string $message
     */
    public function addErrorMessage($message)
    {
        $this->error_messages[] = $message;
    }

    /**
     * Get the names of all fields defined in this form
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

}
