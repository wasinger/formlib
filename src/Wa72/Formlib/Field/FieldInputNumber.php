<?php
namespace Wa72\Formlib\Field;

class FieldInputNumber extends FieldInputText
{
    protected $type = 'number';

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @return FieldInputNumber
     */
    static function createInputNumber($name, $label = '', $value = '')
    {
        return new static($name, $label, $value);
    }
}
