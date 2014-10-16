<?php
namespace Wa72\Formlib\Field;

use Wa72\Formlib\ValidatorEmail;

class FieldInputEmail extends FieldInputText
{
    protected $type = 'email';
    protected $input_filter = FILTER_SANITIZE_EMAIL;
    protected $input_trim = true;

    public function __construct($name, $label = '', $value = '', $error_message = '')
    {
        parent::__construct($name, $label, $value);
        $pass_empty = true;
        $this->setValidator('email', new ValidatorEmail($error_message, $pass_empty));
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param string $error_message
     * @return FieldInputEmail
     */
    static function createInputEmail($name, $label = '', $value = '', $error_message = '')
    {
        return new static($name, $label, $value, $error_message);
    }

}
