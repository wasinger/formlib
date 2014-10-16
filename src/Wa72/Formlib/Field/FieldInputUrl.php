<?php
namespace Wa72\Formlib\Field;

class FieldInputUrl extends FieldInputText
{
    protected $type = 'url';
    protected $input_trim = true;

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @return FieldInputUrl
     */
    static function createInputUrl($name, $label = '', $value = '')
    {
        return new static($name, $label, $value);
    }
}