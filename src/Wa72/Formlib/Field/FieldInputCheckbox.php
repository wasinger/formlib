<?php
namespace Wa72\Formlib\Field;

use Wa72\Formlib\ValidatorInterface;

class FieldInputCheckbox extends Field
{
    protected $checked = false;
    protected $type = 'checkbox';
    /**
     * @var string
     *
     * A string that is returned by getData($for_humans) instead of NULL
     * if the checkbox is not checked and $for_humans is true
     *
     * example: if $this->value is "yes" this could be set to "no"
     */
    protected $unchecked_value_for_humans = 'no';

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param bool $checked
     */
    public function __construct($name, $label, $value, $checked = false)
    {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
        $this->checked = $checked;
    }

    public function setData($value)
    {
        if ((string)$value == $this->value) $checked = true;
        else $checked = false;

        if ($checked !== $this->checked) {
            $this->checked = $checked;
            $this->validation_status = ValidatorInterface::STATUS_NOT_VALIDATED;
        }
    }

    public function getData($for_humans = false)
    {
        if ($this->checked) return $this->value;
        else return ($for_humans ? $this->unchecked_value_for_humans : null);
    }

    public function validate()
    {
        $this->error_messages = array();
        if (count($this->validators)) {
            foreach ($this->validators as $validator) {
                if (!$validator->validate($this->checked)) { // !!! Validate $this->checked instead of $this->value
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

    /**
     * @param boolean $checked
     * @return \Wa72\Formlib\FieldInputCheckbox
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
        return $this;
    }

    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * Set a human readable string that is returned by getData($for_humans) instead of NULL
     * if the checkbox is not checked and $for_humans is true
     *
     * example: if $this->value is "yes" this could be set to "no"
     *
     * @param string $unchecked_value_for_humans
     */
    public function setUncheckedValueForHumans($unchecked_value_for_humans)
    {
        $this->unchecked_value_for_humans = $unchecked_value_for_humans;
    }

    public function getWidget()
    {
        $domdoc = $this->getDOMDocument();
        $domel = $domdoc->createElement('input');
        foreach ($this->attributes as $name => $value) {
            $domel->setAttribute($name, $value);
        }
        $domel->setAttribute('type', $this->type);
        $domel->setAttribute('name', $this->getFullName());
        $domel->setAttribute('value', $this->value);
        $domel->setAttribute('id', $this->getId());

        if ($this->required) $domel->setAttribute('required', true);

        if ($this->checked) $domel->setAttribute('checked', true);

        return $domel;
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param bool $checked
     * @return FieldInputCheckbox
     */
    static function createInputCheckbox($name, $label, $value, $checked = false)
    {
        return new static($name, $label, $value, $checked);
    }

}
