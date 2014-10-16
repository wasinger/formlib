<?php
namespace Wa72\Formlib\Field;

class InputNumber extends InputText
{
    protected $type = 'number';

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @return InputNumber
     */
    static function createInputNumber($name, $label = '', $value = '')
    {
        return new static($name, $label, $value);
    }
}
