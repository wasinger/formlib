<?php
namespace Wa72\Formlib;

class ValidatorRequired implements ValidatorInterface
{
    protected $errormessage;

    public function __construct($errormessage = '')
    {
        if ($errormessage) $this->errormessage = $errormessage;
        else $this->errormessage = ErrorMessages::$required;
    }

    public function validate($value)
    {
        if (is_array($value)) return (bool)count($value);
        else return ('' !== (string)$value);
    }

    public function getErrorMessage()
    {
        return $this->errormessage;
    }
}
