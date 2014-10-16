<?php
namespace Wa72\Formlib\Field;

use Wa72\Formlib\ErrorMessages;
use Wa72\Formlib\ValidatorInterface;

class Select extends Field
{
    protected $choices = array();
    protected $add_empty_choice = null;

    /**
     * @param string $name
     * @param array $choices
     * @param string|null $add_empty_choice Give a label (may be empty an string) for a choice with empty value
     *                                      to be added on top of the choices
     * @param string $label
     * @param string $value
     * @throws \InvalidArgumentException When choices is not an array
     */
    public function __construct($name, $choices, $add_empty_choice = null, $label = '', $value = '')
    {
        parent::__construct($name, $label, $value);
        if (!is_array($choices)) throw new \InvalidArgumentException('choices must be array');
        $this->choices = $choices;
        if ($add_empty_choice !== null) {
            $this->add_empty_choice = $add_empty_choice;
        }
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
        $domel = $domdoc->createElement('select');
        foreach ($this->attributes as $name => $value) {
            $domel->setAttribute($name, $value);
        }
        $domel->setAttribute('name', $this->getFullName());
        $domel->setAttribute('id', $this->getId());
        if ($this->required) $domel->setAttribute('required', true);
        if ($this->add_empty_choice !== null) {
            $optel = $domel->appendChild($domdoc->createElement('option', $this->add_empty_choice));
            $optel->setAttribute('value', '');
        }
        foreach ($this->choices as $value => $label) {
            $optel = $domel->appendChild($domdoc->createElement('option', $label));
            $optel->setAttribute('value', $value);
            if ((string)$value === $this->value) $optel->setAttribute('selected', true);
        }
        return $domel;
    }

    public function validate()
    {
        parent::validate(); // calls registered additional validators, if any
        if ($this->value !== '' && !isset($this->choices[$this->value])) {
            $this->validation_status = ValidatorInterface::STATUS_INVALID;
            $this->error_messages[] = sprintf(ErrorMessages::$wrong_select_value, $this->value);
        }
    }

    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param $name
     * @param $choices
     * @param null $add_empty_choice
     * @param string $label
     * @param string $value
     * @return Select
     */
    static function createSelect($name, $choices, $add_empty_choice = null, $label = '', $value = '')
    {
        return new static($name, $choices, $add_empty_choice, $label, $value);
    }
}
