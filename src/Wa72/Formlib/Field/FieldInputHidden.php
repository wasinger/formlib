<?php
namespace Wa72\Formlib\Field;


class FieldInputHidden extends Field
{
    protected $include_in_data = false;
    protected $is_hidden = true;

    public function __construct($name, $value = '')
    {
        $this->name = $name;
        $this->value = $value;
        $this->include_in_data = false;
        $this->label = null;
        $this->is_hidden = true;
    }

    public function getWidget()
    {
        $domdoc = $this->getDOMDocument();
        $domel = $domdoc->createElement('input');
        foreach ($this->attributes as $name => $value) {
            $domel->setAttribute($name, $value);
        }
        $domel->setAttribute('type', 'hidden');
        $domel->setAttribute('name', $this->getFullName());
        $domel->setAttribute('id', $this->getId());
        $domel->setAttribute('value', $this->value);

        return $domel;
    }

    public function getData($for_humans = false)
    {
        return $this->value;
    }

    /**
     * @param $name
     * @param string $value
     * @return FieldInputHidden
     */
    static function createInputHidden($name, $value = '')
    {
        return new static($name, $value);
    }

}



