<?php
namespace Wa72\Formlib\Field;
use Wa72\Formlib\ValidatorInterface;
use Wa72\Formlib\Form;
use Wa72\Formlib\ValidatorRequired;

/**
 * Field represents an input element or a group of input elements (such as radion buttons and check boxes) of an HTML form
 *
 */
abstract class Field
{
    protected $name;
    protected $value;
    protected $label;

    /**
     * @var array Associative name=>value array for additional html attributes of this field
     */
    protected $attributes = array();

    /**
     * @var boolean
     */
    protected $required;

    /**
     * Reference to the containing Form object
     * @var Form
     */
    public $form;

    /**
     * @var ValidatorInterface[]
     */
    protected $validators;

    /**
     * @var int One of ValidatorInterface::STATUS_NOT_VALIDATED, ValidatorInterface::STATUS_INVALID,ValidatorInterface::STATUS_VALID
     */
    protected $validation_status = ValidatorInterface::STATUS_NOT_VALIDATED;

    /**
     * @var string[]
     */
    protected $error_messages;

    /**
     * Whether this field should be included when displaying or mailing submitted data
     * set to true for normal fields (default)
     * set to false for hidden fields like CSRF-protection
     *
     * @var bool
     */
    protected $include_in_data = true;

    /**
     * whether this is a hidden field
     *
     * @var bool
     */
    protected $is_hidden = false;

    /**
     * the input filter which is called on setData(),
     * one of PHP's FILTER_SANITIZE_... constants.
     * See http://php.net/manual/en/filter.filters.sanitize.php
     *
     * @var int
     *
     */
    protected $input_filter = FILTER_SANITIZE_STRING;

    /**
     * Flags for input_filter
     * see http://php.net/manual/en/filter.filters.sanitize.php
     *
     * @var int
     */
    protected $input_filter_flags = 0;

    /**
     * Whether the input should be trimmed
     *
     * @var bool
     */
    protected $input_trim = false;

    /**
     * @param string $name
     * @param string $label
     * @param string $value An optional default value
     */
    public function __construct($name, $label = '', $value = '')
    {
        $this->name = $name;
        if ($label) {
            $this->label = $label;
        } else {
            $this->label = ucfirst($name);
        }
        if ($value) {
            $this->value = $value;
        }
    }

    /**
     * Gets the plain name of the form element
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the field name in square brackets with the form name prepended
     * as it is needed for the name attribute of the input element
     *
     * @return string
     */
    public function getFullName()
    {
        if ($this->form instanceof Form) {
            return $this->form->getName() . '[' . $this->name . ']';
        } else {
            return $this->name;
        }
    }

    /**
     * Returns the value of the ID attribute of the input element
     *
     * @return string
     */
    public function getId()
    {
        if ($this->form instanceof Form) {
            return $this->form->getName() . '_' . $this->name;
        } else {
            return $this->name;
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Set the (input html attribute) value
     * Do not use this for binding the form to submitted data, use setData() instead!
     *
     * @param string $value
     * @return Field
     * @see setData($value)
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    public function setRequired($required, $errormessage = '')
    {
        $this->required = $required;
        if ($this->required) {
            if (!isset($this->validators['required'])) $this->setValidator('required', new ValidatorRequired($errormessage));
        } else {
            $this->removeValidator('required');
        }
        return $this;
    }

    /**
     * Set a validator for this field. Every validator needs a key under which it is registered.
     * The key should describe the purpose of the validator, e.g. 'required', 'maxlength'
     *
     * @param string $key
     * @param ValidatorInterface $validator
     * @return Field self-reference for chaining
     */
    public function setValidator($key, $validator)
    {
        $this->validators[$key] = $validator;
        return $this;
    }

    public function getValidator($key)
    {
        return (isset($this->validators[$key]) ? $this->validators[$key] : null);
    }

    public function removeValidator($key)
    {
        if (isset($this->validators[$key])) unset($this->validators[$key]);
        return $this;
    }

    /**
     * Set additional attribute of the html tag for this field widget
     *
     * @param string $name
     * @param string $value
     * @return Field self-reference for chaining
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function getAttribute($name)
    {
        return (isset($this->attributes[$name]) ? $this->attributes[$name] : null);
    }

    public function removeAttribute($name)
    {
        if (isset($this->attributes[$name])) unset($this->attributes[$name]);
        return $this;
    }

    /**
     * Set attributes for the html tag of the input element
     *
     * @param array $attributes
     * @throws \InvalidArgumentException
     * @return Field
     */
    public function setAttributes($attributes)
    {
        if (!is_array($attributes)) {
            throw new \InvalidArgumentException('$attributes must be an associative array of name->value pairs');
        }
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
        return $this;
    }

    /**
     * Set the field data, used when binding the form to submitted data
     *
     * @param mixed $value
     * @return Field self-reference for chaining
     * @api
     */
    public function setData($value)
    {
        if (is_scalar($value)) {
            if ($this->input_trim) $value = trim($value);
            $value = filter_var($value, $this->input_filter, $this->input_filter_flags);
        }
        if ($value !== $this->value) {
            $this->value = $value;
            $this->validation_status = ValidatorInterface::STATUS_NOT_VALIDATED;
        }
        return $this;
    }

    public function validate()
    {
        $this->error_messages = array();
        if (count($this->validators)) {
            foreach ($this->validators as $validator) {
                if (!$validator->validate($this->value)) {
                    $this->validation_status = ValidatorInterface::STATUS_INVALID;
                    $this->error_messages[] = $validator->getErrorMessage();
                } else {
                    if ($this->validation_status != ValidatorInterface::STATUS_INVALID) {
                        $this->validation_status = ValidatorInterface::STATUS_VALID;
                    }
                }
            }
        } else { // no validators set: always valid
            $this->validation_status = ValidatorInterface::STATUS_VALID;
        }
    }

    public function getValidationStatus()
    {
        return $this->validation_status;
    }

    public function getErrorMessages()
    {
        return $this->error_messages;
    }

    /**
     * Return the HTML DOM Element of the form input widget
     *
     * @return \DOMElement
     * @api
     */
    abstract function getWidget();

    /**
     * Return the value of the field for further processing
     * Useful for printing the already filled out form
     *
     * @param bool $for_humans if true, return a data representation intended to be read by humans, if available
     * @return string
     * @api
     */
    abstract function getData($for_humans = false);

    /**
     * Return the value, wrapped in a div with class form-data-result, as DOMElement
     *
     * For displaying the data in the form instead of the input widget
     * when presenting the filled out form data in the same layout as the original form
     *
     * @param bool $for_humans
     * @return \DOMElement
     */
    public function getDataWidget($for_humans = true)
    {
        $div = $this->getDOMDocument()->createElement('div', $this->renderData($for_humans));
        $div->setAttribute('id', $this->getId());
        $div->setAttribute('class', 'form-data-result');
        return $div;
    }

    /**
     * @return \DOMDocument
     */
    protected function getDOMDocument()
    {
        if ($this->form instanceof Form) {
            return $this->form->getDomdocument();
        } else {
            return new \DOMDocument('1.0', 'UTF-8');
        }
    }

    /**
     * Return a DOMElement containing the label of this field
     *
     * @return \DOMElement|null if the field has no label
     */
    function getLabelElement()
    {
        if (null === $this->label) return null;
        $domdoc = $this->getDOMDocument();
        $domel = $domdoc->createElement('label', $this->getLabel());
        $domel->setAttribute('for', $this->getId());
        return $domel;
    }

    /**
     * Renders the field value into a string
     *
     * If value is an array, values are concatanated by comma.
     *
     * @param bool $for_humans Use label instead of value for select options, checkboxes and radios
     * @return string
     */
    public function renderData($for_humans = false)
    {
        if (!$this->include_in_data) return '';
        if (is_array($this->getData($for_humans))) {
            return join(', ', $this->getData($for_humans));
        } else {
            return $this->getData($for_humans);
        }
    }

    /**
     * Whether this field should be included when displaying or mailing submitted data.
     *
     * @return bool
     */
    public function getIncludeInData()
    {
        return $this->include_in_data;
    }

    /**
     * Set whether this field should be included when displaying or mailing submitted data.
     * Set to true for normal fields (default).
     * Set to false for hidden fields like CSRF-protection.
     * For hidden fields, the default value ist false, but there may be hidden fields you
     * want to include when displaying submitted data, and displayed fields that you don't
     * (e.g. captchas)
     *
     * @param bool $bool
     */
    public function setIncludeInData($bool)
    {
        $this->include_in_data = $bool;
    }

    /**
     * Whether or not this is a hidden field
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->is_hidden;
    }
}