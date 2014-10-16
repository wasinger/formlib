<?php
namespace Wa72\Formlib\Field;

use Wa72\Formlib\ValidatorMaxlength;
use Wa72\Formlib\ValidatorPattern;

class InputText extends Field
{
    protected $maxlength;
    protected $pattern;
    protected $type = 'text';

    public function getWidget()
    {
        $domdoc = $this->getDOMDocument();
        $domel = $domdoc->createElement('input');
        foreach ($this->attributes as $name => $value) {
            $domel->setAttribute($name, $value);
        }
        $domel->setAttribute('type', $this->type);
        $domel->setAttribute('name', $this->getFullName());
        $domel->setAttribute('id', $this->getId());
        $domel->setAttribute('value', $this->value);
        if ($this->required) $domel->setAttribute('required', true);
        if ($this->maxlength) $domel->setAttribute('maxlength', $this->maxlength);
        if ($this->pattern) $domel->setAttribute('pattern', $this->pattern);

        return $domel;
    }

    public function getMaxlength()
    {
        return $this->maxlength;
    }

    public function setMaxlength($maxlength, $errormessage = '')
    {
        $this->maxlength = $maxlength;
        if ($this->maxlength) {
            if (!isset($this->validators['maxlength'])) $this->setValidator('maxlength', new ValidatorMaxlength($maxlength, $errormessage));
        } else {
            $this->removeValidator('maxlength');
        }
        return $this;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set the pattern for field validation
     *
     * According to the html5 spec, when a pattern is set, there should also be a title attribute describing
     * the required input to the user. Typically this title is not only displayed as tooltip when the mouse is over the input
     * field but also as part of the error message when the pattern does not match. The placeholder is displayed in
     * the input field as long as there is no data, it could be an example for a string matching the pattern.
     *
     * That's why the title an placeholder attributes can optionally be set here together with the pattern for convenience.
     *
     * @param string $pattern Regular expression WITHOUT pattern delimiters!
     * @param string $errormessage
     * @param string $title
     * @param string $placeholder
     * @return InputText
     */
    public function setPattern($pattern, $errormessage = '', $title = '', $placeholder = '')
    {
        $this->pattern = $pattern;
        if ($this->pattern) {
            if (!isset($this->validators['pattern'])) $this->setValidator('pattern', new ValidatorPattern($pattern, $errormessage));
        } else {
            $this->removeValidator('pattern');
        }
        if ($title) $this->setAttribute('title', $title);
        if ($placeholder) $this->setAttribute('placeholder', $placeholder);
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
     * @return \Wa72\Formlib\Field\Field
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
     * @return InputText
     */
    static function createInputText($name, $label = '', $value = '')
    {
        return new static($name, $label, $value);
    }
}
