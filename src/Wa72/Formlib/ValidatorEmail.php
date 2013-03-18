<?php
namespace Wa72\Formlib;

class ValidatorEmail implements ValidatorInterface
{
    protected $errormessage;
    protected $pass_empty;

    public function __construct($errormessage = '', $pass_empty = true)
    {
        if ($errormessage) $this->errormessage = $errormessage;
        else $this->errormessage = ErrorMessages::$email;
        $this->pass_empty = $pass_empty;
    }

    public function validate($value)
    {
        if (!$value && $this->pass_empty) return true;
        return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public function getErrorMessage()
    {
        return $this->errormessage;
    }
}