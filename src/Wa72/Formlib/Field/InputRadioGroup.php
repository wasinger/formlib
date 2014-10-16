<?php
namespace Wa72\Formlib\Field;

use Wa72\Formlib\ErrorMessages;
use Wa72\Formlib\ValidatorInterface;

class InputRadioGroup extends Field
{
    protected $choices = array();

    public function __construct($name, $choices, $label = '', $value = '')
    {
        parent::__construct($name, $label, $value);
        if (!is_array($choices)) throw new \InvalidArgumentException('choices must be array');
        $this->choices = $choices;
    }

    public function getData($for_humans = false)
    {
        if ($this->value !== '' && $for_humans) {
            return $this->choices[$this->value];
        } else return $this->value;
    }

    public function getWidget()
    {
        $domdoc = $this->getDOMDocument();
        $domel = $domdoc->createElement('ul');
        foreach ($this->attributes as $name => $value) {
            $domel->setAttribute($name, $value);
        }
        //$domel->setAttribute('name', $this->getFullName());
        //$domel->setAttribute('id', $this->getId());

        $i = 0;
        foreach ($this->choices as $value => $label) {
            $i++;
            $liel = $domel->appendChild($domdoc->createElement('li'));
            $inel = $liel->appendChild($domdoc->createElement('input'));
            $label = $liel->appendChild($domdoc->createElement('label', $label));
            $label->setAttribute('for', $this->getId() . '_' . $i);
            $inel->setAttribute('id', $this->getId() . '_' . $i);
            $inel->setAttribute('value', $value);
            $inel->setAttribute('type', 'radio');
            $inel->setAttribute('name', $this->getFullName());
            if ((string)$value == $this->value) $inel->setAttribute('checked', true);
        }
        return $domel;
    }

    public function validate()
    {
        parent::validate(); // calls registered additional validators, if any
        if ((string) $this->value !== '' && !isset($this->choices[$this->value])) {
            $this->validation_status = ValidatorInterface::STATUS_INVALID;
            $this->error_messages[] = sprintf(ErrorMessages::$wrong_select_value, $this->value);
        }
    }

    /**
     * @param string $name
     * @param string $choices
     * @param string $label
     * @param string $value
     * @return InputRadioGroup
     */
    static function createInputRadioGroup($name, $choices, $label = '', $value = '')
    {
        return new static($name, $choices, $label, $value);
    }

}
