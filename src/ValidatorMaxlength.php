<?php
namespace Wa72\Formlib;

class ValidatorMaxlength implements ValidatorInterface
{
    protected $errormessage;
    protected $size;
    protected $pass_empty;

    public function __construct($size, $errormessage = '', $pass_empty = true)
    {
        if ($errormessage) $this->errormessage = $errormessage;
        else $this->errormessage = ErrorMessages::$maxlength;
        $this->size = $size;
        $this->pass_empty = $pass_empty;
    }

    public function validate($value)
    {
        if (!$value && $this->pass_empty) return true;
        return (strlen($value) <= $this->size);
    }

    public function getErrorMessage()
    {
        return sprintf($this->errormessage, $this->size);
    }
}
