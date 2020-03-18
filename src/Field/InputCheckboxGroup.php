<?php
namespace Wa72\Formlib\Field;


class InputCheckboxGroup extends Field
{
    protected $value = array();
    protected $choices = array();

    /**
     * @param string $name
     * @param array $choices
     * @param string $label
     * @param array $value
     * @throws \InvalidArgumentException
     */
    public function __construct($name, array $choices, $label = '', $value = array())
    {
        parent::__construct($name, $label, $value);
        if (!is_array($choices)) throw new \InvalidArgumentException('choices must be array');
        $this->choices = $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($for_humans = false)
    {
        if ($for_humans) {
            $data = array();
            if (count($this->value)) {
                foreach ($this->value as $value) {
                    $data[] = (isset($this->choices[$value]) ? $this->choices[$value] : $value);
                }
            }
            return $data;
        } else return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $domdoc = $this->getDOMDocument();
        $domel = $domdoc->createElement('ul');
        foreach ($this->attributes as $name => $value) {
            $domel->setAttribute($name, $value);
        }
        ;

        $i = 0;
        foreach ($this->choices as $value => $label) {
            $i++;
            $liel = $domel->appendChild($domdoc->createElement('li'));
            $inel = $liel->appendChild($domdoc->createElement('input'));
            $label = $liel->appendChild($domdoc->createElement('label', $label));
            $label->setAttribute('for', $this->getId() . '_' . $i);
            $inel->setAttribute('id', $this->getId() . '_' . $i);
            $inel->setAttribute('value', $value);
            $inel->setAttribute('type', 'checkbox');
            $inel->setAttribute('name', $this->getFullName() . '[]');
            if (in_array($value, $this->value)) $inel->setAttribute('checked', true);
            // It's not possible in HTML5 to validate that at least one checkbox is checked.
            // Webshim adds this feature using "data-grouprequired" attribute
            // so we add it here.
            // Webshim js must be included for this to work.
            // See http://afarkas.github.io/webshim/demos/demos/forms.html#Custom-validity
            if ($this->isRequired()) $inel->setAttribute('data-grouprequired', true);
        }
        return $domel;
    }

    /**
     * @param string $name
     * @param string $choices
     * @param string $label
     * @param array $value
     * @return InputCheckboxGroup
     */
    static function createInputCheckboxGroup($name, $choices, $label = '', $value = array())
    {
        return new static($name, $choices, $label, $value);
    }
}

