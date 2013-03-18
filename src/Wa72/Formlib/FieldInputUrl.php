<?php
namespace Wa72\Formlib;

class FieldInputUrl extends FieldInputText
{
    protected $type = 'url';

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @return FieldInputText
     */
    static function createInputUrl($name, $label = '', $value = '')
    {
        return new static($name, $label, $value);
    }
}