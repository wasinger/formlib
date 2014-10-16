<?php
namespace Wa72\Formlib\Field;

class InputUrl extends InputText
{
    protected $type = 'url';
    protected $input_trim = true;

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @return InputUrl
     */
    static function createInputUrl($name, $label = '', $value = '')
    {
        return new static($name, $label, $value);
    }
}