<?php
namespace Wa72\Formlib\Field;

class InputTel extends InputText
{
    protected $type = 'tel';
    protected $input_trim = true;

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @return InputTel
     */
    static function createInputTel($name, $label = '', $value = '')
    {
        return new static($name, $label, $value);
    }

}