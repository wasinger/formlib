<?php
namespace Wa72\Formlib\Field;
use Wa72\Formlib\ValidatorMaxlength;

class Textarea extends Field
{
    protected $maxlength;

    public function __construct($name, $label = '', $value = '')
    {
        parent::__construct($name, $label, $value);
        $this->input_filter_flags = $this->input_filter_flags | !FILTER_FLAG_STRIP_LOW; // don't remove newlines and tabs
    }

    public function getWidget()
    {
        $domdoc = $this->getDOMDocument();
        $domel = $domdoc->createElement('textarea', $this->value);
        foreach ($this->attributes as $name => $value) {
            $domel->setAttribute($name, $value);
        }
        $domel->setAttribute('name', $this->getFullName());
        $domel->setAttribute('id', $this->getId());
        if ($this->required) $domel->setAttribute('required', true);
        if ($this->maxlength) $domel->setAttribute('maxlength', $this->maxlength);

        return $domel;
    }

    public function getMaxlength()
    {
        return $this->maxlength;
    }

    public function setMaxlength($maxlength)
    {
        $this->maxlength = $maxlength;
        if ($this->maxlength) {
            if (!isset($this->validators['maxlength'])) $this->setValidator('maxlength', new ValidatorMaxlength($maxlength));
        } else {
            $this->removeValidator('maxlength');
        }
        return $this;
    }

    public function getData($for_humans = false)
    {
        return $this->value;
    }

    /**
     * Set the field data, used when binding the form to submitted data
     *
     * @param string $value
     * @return \Wa72\Formlib\Field\Textarea
     */
    public function setData($value)
    {
        $value = (string)$value;
        return parent::setData($value);
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @return Textarea
     */
    static function createTextarea($name, $label = '', $value = '')
    {
        return new static($name, $label, $value);
    }

}
