<?php
namespace Wa72\Formlib;

class FieldInputNumber extends FieldInputText
{
    protected $type = 'number';

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @return FieldInputText
     */
    static function createInputNumber($name, $label = '', $value = '')
    {
        return new static($name, $label, $value);
    }
}
